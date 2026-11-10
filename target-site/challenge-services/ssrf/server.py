#!/usr/bin/env python3
"""
E CORP - PRIVATE INTERNAL MICROSERVICE (SSRF Target)
This service simulates an isolated backend cloud metadata / internal telemetry service.
It runs on port 8080 inside an isolated docker network.
"""

from http.server import HTTPServer, BaseHTTPRequestHandler
import json
import os

PORT = int(os.environ.get("SSRF_PORT", 8080))
FLAG = os.environ.get("FLAG_A01", "Flagfound{internal_affairs_728}")

class InternalServiceHandler(BaseHTTPRequestHandler):
    def log_message(self, format, *args):
        # Silent or log to stdout
        print(f"[INTERNAL-SRV] {self.command} {self.path} - {format % args}")

    def do_GET(self):
        self.send_response(200)
        self.send_header("Content-Type", "application/json")
        self.send_header("X-Internal-Service", "E-Corp-Node-09")
        self.end_headers()

        if self.path in ["/internal/system/flag", "/internal/flag", "/flag", "/admin/flag"]:
            payload = {
                "status": "success",
                "node": "core-internal-cluster-alpha",
                "clearance": "RESTRICTED",
                "message": "Authorized internal cluster access verified.",
                "flag": FLAG,
                "token": "INT-AFF-728-ROOT"
            }
        elif self.path in ["/", "/health", "/status"]:
            payload = {
                "service": "E Corp Internal Infrastructure Gateway",
                "status": "OPERATIONAL",
                "endpoints": [
                    "/internal/status",
                    "/internal/system/flag",
                    "/metadata/instance"
                ]
            }
        elif self.path == "/metadata/instance":
            payload = {
                "instance_id": "i-098234abcf912",
                "zone": "zone-us-east-ecorp-1",
                "role": "InternalSecurityNode",
                "secret_vault": "/internal/system/flag"
            }
        else:
            payload = {
                "error": "Not Found",
                "path": self.path,
                "hint": "Check /metadata/instance or /internal/system/flag"
            }

        self.wfile.write(json.dumps(payload, indent=2).encode('utf-8'))

def run():
    server_address = ('0.0.0.0', PORT)
    httpd = HTTPServer(server_address, InternalServiceHandler)
    print(f"[+] E Corp Internal Microservice listening on port {PORT}...")
    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        pass
    httpd.server_close()

if __name__ == '__main__':
    run()
