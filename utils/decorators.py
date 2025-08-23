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
