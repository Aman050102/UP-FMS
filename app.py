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
