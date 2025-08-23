def is_up_email(email: str, allowed_domain: str) -> bool:
    if not email or "@" not in email:
        return False
    return email.split("@")[-1].lower() == allowed_domain.lower()
def infer_role_from_button(choice: str):
    if (choice or "").lower() == "staff":
        return True, "staff"
    return False, "user"
