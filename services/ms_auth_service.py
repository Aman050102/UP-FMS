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
