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
