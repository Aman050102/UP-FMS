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
