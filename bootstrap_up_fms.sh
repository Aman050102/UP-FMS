set -e

mkdir -p services repositories utils templates static/css static/js static/img

# requirements
cat > requirements.txt <<'R'
Flask==3.0.3
python-dotenv==1.0.1
msal==1.29.0
Flask-Session==0.8.0
R

# .env example
cat > .env.example <<'E'
FLASK_SECRET_KEY=change_me_to_a_long_random_value
FLASK_ENV=development
MS_CLIENT_ID=YOUR_APP_CLIENT_ID
MS_CLIENT_SECRET=YOUR_APP_CLIENT_SECRET
MS_TENANT_ID=YOUR_TENANT_ID
MS_AUTHORITY=https://login.microsoftonline.com/${MS_TENANT_ID}
MS_REDIRECT_PATH=/auth/callback
MS_SCOPES=openid profile email
UP_ALLOWED_DOMAIN=up.ac.th
E

# config.py
cat > config.py <<'PY'
import os
from dotenv import load_dotenv
load_dotenv()
class Config:
    SECRET_KEY = os.getenv("FLASK_SECRET_KEY", "dev")
    SESSION_TYPE = "filesystem"
    MS_CLIENT_ID = os.getenv("MS_CLIENT_ID")
    MS_CLIENT_SECRET = os.getenv("MS_CLIENT_SECRET")
    MS_TENANT_ID = os.getenv("MS_TENANT_ID")
    MS_AUTHORITY = os.getenv("MS_AUTHORITY") or f"https://login.microsoftonline.com/{MS_TENANT_ID}"
    MS_REDIRECT_PATH = os.getenv("MS_REDIRECT_PATH", "/auth/callback")
    MS_SCOPES = (os.getenv("MS_SCOPES", "openid profile email").split())
    UP_ALLOWED_DOMAIN = os.getenv("UP_ALLOWED_DOMAIN", "up.ac.th")
PY

# models.py
cat > models.py <<'PY'
from dataclasses import dataclass
from enum import Enum
class Role(Enum):
    USER = "user"
    STAFF = "staff"
@dataclass
class User:
    id: str
    display_name: str
    email: str
    role: Role
    @property
    def is_staff(self) -> bool:
        return self.role == Role.STAFF
PY

# repository
cat > repositories/user_repository.py <<'PY'
from typing import Dict, Optional
from models import User
class UserRepository:
    def __init__(self):
        self._users: Dict[str, User] = {}
    def upsert(self, user: User) -> None:
        self._users[user.id] = user
    def get(self, user_id: str) -> Optional[User]:
        return self._users.get(user_id)
    def get_by_email(self, email: str) -> Optional[User]:
        email = (email or "").lower()
        for u in self._users.values():
            if u.email.lower() == email:
                return u
        return None
PY

# services
cat > services/auth_base.py <<'PY'
from abc import ABC, abstractmethod
from typing import Dict, Optional
class AuthService(ABC):
    @abstractmethod
    def build_auth_url(self) -> str: ...
    @abstractmethod
    def redeem_token(self, auth_response_params: Dict[str, str]) -> Dict: ...
    @abstractmethod
    def get_user_claims(self, token_result: Dict) -> Optional[Dict]: ...
PY

cat > services/ms_auth_service.py <<'PY'
import msal
from typing import Dict, Optional
from flask import url_for
class MicrosoftAuthService:
    def __init__(self, flask_config):
        self.cfg = flask_config
        self.app = msal.ConfidentialClientApplication(
            client_id=self.cfg["MS_CLIENT_ID"],
            client_credential=self.cfg["MS_CLIENT_SECRET"],
            authority=self.cfg["MS_AUTHORITY"],
        )
    def build_auth_url(self) -> str:
        return self.app.get_authorization_request_url(
            scopes=self.cfg["MS_SCOPES"],
            redirect_uri=url_for("auth_callback", _external=True),
            prompt="select_account",
        )
    def redeem_token(self, auth_response_params: Dict[str, str]) -> Dict:
        code = auth_response_params.get("code")
        result = self.app.acquire_token_by_authorization_code(
            code=code,
            scopes=self.cfg["MS_SCOPES"],
            redirect_uri=url_for("auth_callback", _external=True),
        )
        if "error" in result:
            raise RuntimeError(result.get("error_description", "Auth failed"))
        return result
    def get_user_claims(self, token_result: Dict) -> Optional[Dict]:
        return token_result.get("id_token_claims", {})
PY

# utils
cat > utils/validators.py <<'PY'
def is_up_email(email: str, allowed_domain: str) -> bool:
    if not email or "@" not in email:
        return False
    return email.split("@")[-1].lower() == allowed_domain.lower()
def infer_role_from_button(choice: str):
    if (choice or "").lower() == "staff":
        return True, "staff"
    return False, "user"
PY

cat > utils/decorators.py <<'PY'
from functools import wraps
from flask import session, redirect, url_for, abort
from models import Role
def login_required(view):
    @wraps(view)
    def wrapped(*args, **kwargs):
        if not session.get("user_id"):
            return redirect(url_for("login"))
        return view(*args, **kwargs)
    return wrapped
def role_required(required: Role):
    def decorator(view):
        @wraps(view)
        def wrapped(*args, **kwargs):
            if not session.get("user_role"):
                return redirect(url_for("login"))
            if session["user_role"] != required.value:
                return abort(403)
            return view(*args, **kwargs)
        return wrapped
    return decorator
PY

# app.py
cat > app.py <<'PY'
from flask import Flask, render_template, redirect, request, session, url_for
from flask_session import Session
from config import Config
from models import User, Role
from repositories.user_repository import UserRepository
from services.ms_auth_service import MicrosoftAuthService
from utils.validators import is_up_email, infer_role_from_button
from utils.decorators import login_required
app = Flask(__name__)
app.config.from_object(Config)
Session(app)
user_repo = UserRepository()
auth = MicrosoftAuthService(app.config)
@app.get("/")
def login():
    return render_template("login.html")
@app.post("/auth/start")
def auth_start():
    role_choice = request.form.get("role_choice", "user")
    session["role_choice"] = role_choice
    return redirect(auth.build_auth_url())
@app.get(app.config["MS_REDIRECT_PATH"])
def auth_callback():
    token = auth.redeem_token(request.args)
    claims = auth.get_user_claims(token)
    email = (claims.get("preferred_username") or claims.get("email") or "").lower()
    name = claims.get("name", "UP Account")
    if not is_up_email(email, app.config["UP_ALLOWED_DOMAIN"]):
        return "อนุญาตเฉพาะอีเมลโดเมนของมหาวิทยาลัยเท่านั้น", 403
    _, role_str = infer_role_from_button(session.get("role_choice", "user"))
    role = Role.STAFF if role_str == "staff" else Role.USER
    uid = claims.get("oid") or claims.get("sub") or email
    user = User(id=uid, display_name=name, email=email, role=role)
    user_repo.upsert(user)
    session["user_id"] = user.id
    session["user_name"] = user.display_name
    session["user_email"] = user.email
    session["user_role"] = user.role.value
    return redirect(url_for("dashboard"))
@app.get("/dashboard")
@login_required
def dashboard():
    if session["user_role"] == Role.STAFF.value:
        return render_template("dashboard_staff.html", name=session["user_name"])
    return render_template("dashboard_user.html", name=session["user_name"])
@app.get("/logout")
@login_required
def logout():
    session.clear()
    return redirect(url_for("login"))
# Dev route for UI testing without OAuth (optional)
@app.get("/auth/dev")
def auth_dev():
    role_choice = session.get("role_choice", "user")
    role = Role.STAFF if role_choice == "staff" else Role.USER
    user = User(id="dev-user-1", display_name="Dev Tester", email="tester@up.ac.th", role=role)
    session["user_id"] = user.id
    session["user_name"] = user.display_name
    session["user_email"] = user.email
    session["user_role"] = user.role.value
    return redirect(url_for("dashboard"))
if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)
PY

# templates
cat > templates/base.html <<'H'
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>{% block title %}DCSQ – Login{% endblock %}</title>
  <link rel="stylesheet" href="{{ url_for('static', filename='css/styles.css') }}">
</head>
<body>
  <header class="topbar"></header>
  <main>
    {% block content %}{% endblock %}
  </main>
  <script src="{{ url_for('static', filename='js/app.js') }}" defer></script>
</body>
</html>
H

cat > templates/login.html <<'H'
{% extends 'base.html' %}
{% block title %}เข้าสู่ระบบ | DCSQ{% endblock %}
{% block content %}
<section class="login-wrapper">
  <div class="card">
    <div class="tabs" role="tablist">
      <button id="tab-staff" class="tab active" data-role="staff" aria-selected="true">สำหรับเจ้าหน้าที่</button>
      <button id="tab-user" class="tab" data-role="user" aria-selected="false">สำหรับบุคลากร/นิสิต</button>
    </div>
    <div class="logo-box">
      <img src="{{ url_for('static', filename='img/logo_placeholder.svg') }}" alt="DCSQ Logo" class="logo" />
      <h1 class="subtitle">กองพัฒนาคุณภาพนิสิตและนิสิตพิการ</h1>
    </div>
    <!-- เปลี่ยน action เป็น auth_start เมื่อพร้อมต่อ OAuth -->
    <form class="action" action="{{ url_for('auth_dev') }}" method="get">
      <input type="hidden" id="role_choice" name="role_choice" value="staff" />
      <button type="submit" class="primary-btn">เข้าสู่ระบบด้วย UP ACCOUNT</button>
    </form>
    <div class="forgot">
      <a href="https://passwordreset.microsoftonline.com/" target="_blank" rel="noopener">ลืมรหัสผ่าน</a>
    </div>
  </div>
</section>
{% endblock %}
H

cat > templates/dashboard_user.html <<'H'
{% extends 'base.html' %}
{% block title %}แดชบอร์ดผู้ใช้งาน{% endblock %}
{% block content %}
<section class="dash">
  <h2>สวัสดี {{ name }} 👋</h2>
  <p>คุณเข้าสู่ระบบในบทบาท <strong>ผู้ใช้สนามกีฬา</strong></p>
  <nav class="dash-nav">
    <a class="link" href="#">จองสนาม</a>
    <a class="link" href="#">ประวัติการใช้งาน</a>
    <a class="link" href="{{ url_for('logout') }}">ออกจากระบบ</a>
  </nav>
</section>
{% endblock %}
H

cat > templates/dashboard_staff.html <<'H'
{% extends 'base.html' %}
{% block title %}แดชบอร์ดเจ้าหน้าที่{% endblock %}
{% block content %}
<section class="dash">
  <h2>สวัสดี {{ name }} 🛠️</h2>
  <p>คุณเข้าสู่ระบบในบทบาท <strong>เจ้าหน้าที่ดูแลสนาม</strong></p>
  <nav class="dash-nav">
    <a class="link" href="#">จัดตารางสนาม</a>
    <a class="link" href="#">ยืม-คืนอุปกรณ์</a>
    <a class="link" href="{{ url_for('logout') }}">ออกจากระบบ</a>
  </nav>
</section>
{% endblock %}
H

# static
cat > static/css/styles.css <<'C'
:root {
  --purple: #6E47B9;
  --bg: #EFE9F6;
  --card: #ffffff;
  --text: #1f1f1f;
  --muted: #6b7280;
}
* { box-sizing: border-box; }
body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; color: var(--text); background: var(--bg); }
.topbar { height: 64px; background: var(--purple); }
.login-wrapper { display: grid; place-items: center; min-height: calc(100vh - 64px); padding: 24px; }
.card { width: 100%; max-width: 560px; background: var(--card); border-radius: 20px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,.08); }
.tabs { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
.tab { padding: 12px 16px; border-radius: 12px; border: 2px solid #e5e7eb; background: #f8fafc; cursor: pointer; font-weight: 600; }
.tab.active { background: #ede9fe; border-color: var(--purple); color: var(--purple); }
.logo-box { text-align: center; margin: 18px 0 10px; }
.logo { width: 280px; max-width: 70%; height: auto; }
.subtitle { font-size: 14px; color: var(--muted); margin-top: 8px; }
.primary-btn { width: 100%; padding: 14px 16px; border-radius: 14px; background: #e9e9f8; border: none; font-weight: 700; font-size: 16px; }
.primary-btn:hover { filter: brightness(.98); }
.action { margin: 20px 0; }
.forgot { text-align: center; margin-top: 16px; }
.forgot a { color: #374151; text-decoration: none; }
.forgot a:hover { text-decoration: underline; }
.dash { max-width: 960px; margin: 24px auto; padding: 0 16px; }
.dash-nav { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 12px; }
.link { padding: 10px 14px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; text-decoration: none; color: var(--text); }
.link:hover { background: #f9fafb; }
@media (min-width: 640px) {
  .card { padding: 32px; }
  .primary-btn { font-size: 18px; }
}
C

cat > static/js/app.js <<'J'
document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.tab');
  const roleInput = document.getElementById('role_choice');
  tabs.forEach(btn => {
    btn.addEventListener('click', () => {
      tabs.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const role = btn.dataset.role || 'user';
      roleInput.value = role;
    });
  });
});
J

cat > static/img/logo_placeholder.svg <<'S'
<svg xmlns="http://www.w3.org/2000/svg" width="640" height="200" viewBox="0 0 640 200">
  <rect width="640" height="200" rx="20" fill="#ede9fe"/>
  <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
        font-family="Segoe UI, Arial" font-size="28" fill="#6E47B9">
    DCSQ • UP Sports
  </text>
</svg>
S

echo "Scaffold complete."
