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
