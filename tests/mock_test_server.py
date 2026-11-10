#!/usr/bin/env python3
"""
PROJECT: BLACKOUT — COMPREHENSIVE LOCAL TEST RUNNER & TEST HARNESS
Serves the Target Corporate Site for end-to-end exploit testing of all 20 challenges.
"""

from http.server import HTTPServer, BaseHTTPRequestHandler
import json
import urllib.parse
import os
import re
import time
import base64
import threading

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
TARGET_PUBLIC = os.path.join(BASE_DIR, "target-site", "public")

# Central Flags
FLAGS = {
    'E01': {'flag': 'Flagfound{source_code_ghost_91}', 'pts': 50, 'name': 'Ghost in the Source', 'diff': 'Easy'},
    'E02': {'flag': 'Flagfound{robots_path_discovered_27}', 'pts': 50, 'name': 'Forbidden Path', 'diff': 'Easy'},
    'E03': {'flag': 'Flagfound{cookie_monster_583}', 'pts': 50, 'name': 'Cookie Monster', 'diff': 'Easy'},
    'M01': {'flag': 'Flagfound{identity_exposed_402}', 'pts': 100, 'name': 'Hidden Identity', 'diff': 'Medium'},
    'M02': {'flag': 'Flagfound{parameter_master_719}', 'pts': 100, 'name': 'Parameter Manipulator', 'diff': 'Medium'},
    'M03': {'flag': 'Flagfound{directory_detective_625}', 'pts': 100, 'name': 'Directory Detective', 'diff': 'Medium'},
    'M04': {'flag': 'Flagfound{javascript_secrets_841}', 'pts': 100, 'name': 'JavaScript Secrets', 'diff': 'Medium'},
    'M05': {'flag': 'Flagfound{reflected_nightmare_963}', 'pts': 100, 'name': 'Reflected Nightmare', 'diff': 'Medium'},
    'M06': {'flag': 'Flagfound{forgotten_endpoint_318}', 'pts': 100, 'name': 'Forgotten Endpoint', 'diff': 'Medium'},
    'M07': {'flag': 'Flagfound{broken_login_572}', 'pts': 100, 'name': 'Broken Login', 'diff': 'Medium'},
    'H01': {'flag': 'Flagfound{database_reaper_746}', 'pts': 200, 'name': 'Database Reaper', 'diff': 'Hard'},
    'H02': {'flag': 'Flagfound{shadow_directory_219}', 'pts': 200, 'name': 'Shadow Directory', 'diff': 'Hard'},
    'H03': {'flag': 'Flagfound{black_mirror_853}', 'pts': 200, 'name': 'Black Mirror', 'diff': 'Hard'},
    'H04': {'flag': 'Flagfound{broken_crown_691}', 'pts': 200, 'name': 'Broken Crown', 'diff': 'Hard'},
    'H05': {'flag': 'Flagfound{dangerous_attachment_435}', 'pts': 200, 'name': 'Dangerous Attachment', 'diff': 'Hard'},
    'A01': {'flag': 'Flagfound{internal_affairs_728}', 'pts': 300, 'name': 'Internal Affairs', 'diff': 'Advanced'},
    'A02': {'flag': 'Flagfound{zero_trust_946}', 'pts': 300, 'name': 'Zero Trust', 'diff': 'Advanced'},
    'A03': {'flag': 'Flagfound{graphql_mystery_512}', 'pts': 300, 'name': 'GraphQL Mystery', 'diff': 'Advanced'},
    'A04': {'flag': 'Flagfound{race_condition_374}', 'pts': 300, 'name': 'Race Against Time', 'diff': 'Advanced'},
    'A05': {'flag': 'Flagfound{phantom_admin_187}', 'pts': 300, 'name': 'The Phantom Admin', 'diff': 'Advanced'}
}

# In-memory test state
rewards_state = {"credits": 10, "coupons_claimed": 0}
comments_state = []

class TargetHandler(BaseHTTPRequestHandler):
    def log_message(self, format, *args):
        pass

    def do_GET(self):
        parsed = urllib.parse.urlparse(self.path)
        path = parsed.path
        query = urllib.parse.parse_qs(parsed.query)

        # Root entry loads index.html
        if path == "/" or path == "":
            self.serve_file(os.path.join(TARGET_PUBLIC, "index.html"), "text/html", set_cookie=True)
            return

        # Check static files
        clean_path = path.lstrip("/")
        candidate = os.path.join(TARGET_PUBLIC, clean_path)
        if os.path.isdir(candidate):
            candidate = os.path.join(candidate, "index.html")

        if os.path.isfile(candidate) and not candidate.endswith(".php"):
            mime = "text/html"
            if candidate.endswith(".css"): mime = "text/css"
            elif candidate.endswith(".js"): mime = "application/javascript"
            elif candidate.endswith(".txt"): mime = "text/plain"
            elif candidate.endswith(".json"): mime = "application/json"
            self.serve_file(candidate, mime)
            return

        # ----------------- TARGET API ENDPOINTS -----------------
        if path == "/api/employees.php":
            emp_id = query.get("id", ["1"])[0]
            if emp_id == "99":
                res = {
                    "status": "success",
                    "challenge": "M01",
                    "flag": FLAGS["M01"]["flag"],
                    "profile": {
                        "id": 99,
                        "name": "Alexander Vance",
                        "title": "Chief Security Architect",
                        "bio": f"Clearance Token: {FLAGS['M01']['flag']}"
                    }
                }
                self.send_json(res)
            elif emp_id == "3":
                self.send_json({
                    "status": "success",
                    "profile": {
                        "id": 3,
                        "first_name": "Alden",
                        "last_name": "Cross",
                        "badge_id": "BDG-1003",
                        "title": "Infrastructure Director",
                        "role": "ROLE_SECOPS_DIR"
                    }
                })
            else:
                self.send_json({
                    "status": "success",
                    "profile": {"id": int(emp_id) if emp_id.isdigit() else 1, "name": "John Doe", "badge": "BDG-1001"}
                })
            return

        if path == "/api/services.php":
            tier = query.get("tier", ["standard"])[0].lower()
            if tier in ["classified", "internal", "executive", "secret"]:
                self.send_json({
                    "status": "success",
                    "challenge": "M02",
                    "requested_tier": tier,
                    "details": {
                        "name": "Classified Darknet Enclave",
                        "flag": FLAGS["M02"]["flag"]
                    }
                })
            else:
                self.send_json({"status": "success", "requested_tier": tier, "details": {"name": f"{tier.capitalize()} Tier"}})
            return

        if path == "/api/debug-probe.php":
            key = query.get("key", [""])[0]
            if key == "EC_DEV_DEBUG_KEY_8412":
                self.send_json({"status": "success", "challenge": "M04", "flag": FLAGS["M04"]["flag"]})
            else:
                self.send_json({"status": "unauthorized", "message": "Invalid key"}, 403)
            return

        if path == "/api/search.php":
            q = query.get("q", [""])[0]
            is_xss = any(x in q.lower() for x in ["<script", "onerror=", "onload=", "<svg", "<img", "javascript:", "alert("])
            self.send_json({
                "status": "success",
                "query_raw": q,
                "xss_proof_detected": is_xss,
                "flag": FLAGS["M05"]["flag"] if is_xss else None
            })
            return

        if path == "/api/telemetry.php":
            self.send_json({
                "status": "success",
                "challenge": "M06",
                "flag": FLAGS["M06"]["flag"],
                "unresolved_debug_tokens": {"LEGACY_SYS_REPAIR": FLAGS["M06"]["flag"]}
            })
            return

        if path == "/api/news.php":
            search = query.get("search", [""])[0]
            if "union" in search.lower() or "challenge_records" in search.lower() or "1=1" in search.lower():
                self.send_json({
                    "status": "success",
                    "articles": [
                        {"id": 1, "title": "H01 Challenge Record", "content": FLAGS["H01"]["flag"], "flag": FLAGS["H01"]["flag"]}
                    ]
                })
            else:
                self.send_json({
                    "status": "success",
                    "articles": [{"id": 1, "title": "Enterprise Cybersecurity 2026", "summary": "Quantum cloud resiliency."}]
                })
            return

        if path == "/api/documents.php":
            req_file = query.get("file", [""])[0]
            if "H02_shadow.txt" in req_file or "challenge-secrets" in req_file:
                secret_path = os.path.join(BASE_DIR, "target-site", "challenge-secrets", "hard", "H02_shadow.txt")
                if os.path.exists(secret_path):
                    with open(secret_path, "r", encoding="utf-8") as f:
                        content = f.read()
                    self.send_response(200)
                    self.send_header("Content-Type", "text/plain")
                    self.end_headers()
                    self.wfile.write(content.encode())
                    return
            self.send_json({"status": "error", "message": "File not found"}, 404)
            return

        if path == "/api/feedback.php":
            action = query.get("action", ["list"])[0]
            if action == "moderator_review":
                has_xss = any(any(x in c.lower() for x in ["<script", "onerror=", "onload=", "<svg", "<img"]) for c in comments_state)
                self.send_json({
                    "status": "success",
                    "stored_xss_executed": has_xss,
                    "flag": FLAGS["H03"]["flag"] if has_xss else None
                })
                return
            self.send_json({"status": "success", "comments": comments_state})
            return

        if path in ["/api/auth.php", "/api/admin/vault.php"]:
            action = query.get("action", ["status"])[0]
            if action in ["verify_crown", "protected_vault"]:
                auth_hdr = self.headers.get("Authorization", "")
                token = auth_hdr.replace("Bearer ", "").strip()
                parts = token.split(".")
                if len(parts) >= 2:
                    try:
                        hdr = json.loads(base64.urlsafe_b64decode(parts[0] + "=="))
                        pld = json.loads(base64.urlsafe_b64decode(parts[1] + "=="))
                        if hdr.get("alg", "").lower() == "none" and pld.get("role", "").lower() == "admin":
                            self.send_json({
                                "status": "success",
                                "challenge": "H04",
                                "flag": FLAGS["H04"]["flag"]
                            })
                            return
                    except Exception:
                        pass
                self.send_json({"status": "denied", "message": "Invalid JWT"}, 403)
                return

        if path == "/api/upload.php":
            file_name = query.get("file", [""])[0]
            is_bypass = any(file_name.lower().endswith(x) for x in [".phtml", ".php", ".php5", ".phar"]) or ".php." in file_name.lower()
            if is_bypass:
                self.send_json({"status": "success", "challenge": "H05", "flag": FLAGS["H05"]["flag"]})
            else:
                self.send_json({"status": "success", "message": "Normal file"})
            return

        if path == "/api/fetch.php":
            target_url = query.get("url", [""])[0]
            if "8080" in target_url and "flag" in target_url:
                self.send_json({
                    "status": "success",
                    "challenge": "A01",
                    "flag": FLAGS["A01"]["flag"],
                    "data": {"flag": FLAGS["A01"]["flag"], "service": "E Corp Internal"}
                })
            else:
                self.send_json({"status": "success", "fetched": target_url})
            return

        if path == "/api/audit.php":
            role_hdr = self.headers.get("X-User-Role", "")
            if role_hdr.lower() in ["securitycouncil", "admin", "executive"]:
                self.send_json({"status": "authorized", "challenge": "A02", "flag": FLAGS["A02"]["flag"]})
            else:
                self.send_json({"status": "denied", "message": "403 Forbidden"}, 403)
            return

        if path == "/api/rewards.php":
            action = query.get("action", ["status"])[0]
            if action == "reset":
                rewards_state["credits"] = 10
                rewards_state["coupons_claimed"] = 0
                self.send_json({"status": "success", "balance": 10})
                return
            if action == "claim_bonus":
                if rewards_state["coupons_claimed"] == 0:
                    time.sleep(0.08)
                    rewards_state["credits"] += 10
                    rewards_state["coupons_claimed"] += 1
                    self.send_json({"status": "success", "new_balance": rewards_state["credits"]})
                else:
                    self.send_json({"status": "rejected", "message": "Already claimed"}, 409)
                return
            if action == "purchase_dossier":
                if rewards_state["credits"] >= 20:
                    self.send_json({"status": "success", "challenge": "A04", "flag": FLAGS["A04"]["flag"]})
                else:
                    self.send_json({"status": "insufficient_funds", "credits": rewards_state["credits"]}, 402)
                return
            self.send_json({"status": "success", "balance": rewards_state["credits"], "claimed": rewards_state["coupons_claimed"]})
            return

        if path == "/api/admin-vault.php":
            badge = self.headers.get("X-Auth-Badge", query.get("badge", [""])[0])
            action = query.get("action", ["status"])[0]
            if badge != "BDG-1003":
                self.send_json({"status": "unauthorized", "message": "Requires Director clearance"}, 401)
                return
            if action == "request_elevation":
                role = query.get("role", [""])[0]
                if role == "ROLE_SECOPS_DIR":
                    self.send_json({
                        "status": "elevation_granted",
                        "elevation_token": "ELEV-ALDEN-BDG1003-VALID"
                    })
                    return
            if action == "unlock_vault":
                token = query.get("token", [""])[0]
                if token == "ELEV-ALDEN-BDG1003-VALID":
                    self.send_json({
                        "status": "authorized",
                        "challenge": "A05",
                        "flag": FLAGS["A05"]["flag"]
                    })
                    return
            self.send_json({"status": "identified", "badge": badge})
            return

        self.send_json({"status": "not_found"}, 404)

    def do_POST(self):
        content_len = int(self.headers.get('Content-Length', 0))
        body = self.rfile.read(content_len).decode('utf-8', errors='ignore')
        parsed = urllib.parse.urlparse(self.path)
        path = parsed.path
        query = urllib.parse.parse_qs(parsed.query)

        data = {}
        try:
            data = json.loads(body)
        except Exception:
            data = urllib.parse.parse_qs(body)

        if path == "/api/auth.php" and query.get("action", [""])[0] == "login":
            username = data.get("username", "")
            password = data.get("password", "")
            if username == "admin" and (password is True or password == "true" or isinstance(password, bool)):
                self.send_json({"status": "success", "challenge": "M07", "flag": FLAGS["M07"]["flag"]})
                return
            self.send_json({"status": "error", "message": "Invalid credentials"}, 401)
            return

        if path == "/api/feedback.php" and query.get("action", ["submit"])[0] == "submit":
            comment = data.get("comment", "")
            if isinstance(comment, list): comment = comment[0]
            comments_state.append(comment)
            self.send_json({"status": "success", "message": "Feedback submitted."})
            return

        if path == "/api/upload.php":
            is_bypass = any(x in body.lower() or x in self.path.lower() for x in [".phtml", ".php", ".php5"])
            if is_bypass:
                self.send_json({"status": "success", "challenge": "H05", "flag": FLAGS["H05"]["flag"]})
            else:
                self.send_json({"status": "success", "message": "Uploaded"})
            return

        if path == "/api/graphql.php":
            q_str = data.get("query", "")
            if "_internalSecurityVault" in q_str:
                self.send_json({
                    "data": {
                        "_internalSecurityVault": {
                            "vaultId": "VAULT-ALPHA-99",
                            "flag": FLAGS["A03"]["flag"]
                        }
                    }
                })
            elif "__schema" in q_str:
                self.send_json({
                    "data": {"__schema": {"types": [{"name": "Query", "fields": [{"name": "_internalSecurityVault"}]}]}}
                })
            else:
                self.send_json({"data": None})
            return

        self.send_json({"status": "not_found"}, 404)

    def serve_file(self, file_path, content_type, set_cookie=False):
        if not os.path.exists(file_path):
            self.send_json({"status": "file_not_found"}, 404)
            return
        with open(file_path, "rb") as f:
            content = f.read()
        self.send_response(200)
        self.send_header("Content-Type", content_type)
        if set_cookie:
            val = base64.b64encode(FLAGS['E03']['flag'].encode()).decode()
            self.send_header("Set-Cookie", f"ecorp_session_token=SEC-SESS-9821-{val}; Path=/")
        self.end_headers()
        self.wfile.write(content)

    def send_json(self, data, code=200):
        self.send_response(code)
        self.send_header("Content-Type", "application/json")
        self.end_headers()
        self.wfile.write(json.dumps(data, indent=2).encode())


def start_target_server():
    target_server = HTTPServer(('127.0.0.1', 8081), TargetHandler)
    t = threading.Thread(target=target_server.serve_forever, daemon=True)
    t.start()
    print("[+] Mock Target Server running on http://127.0.0.1:8081")
    return target_server

if __name__ == '__main__':
    start_target_server()
    while True:
        time.sleep(1)
