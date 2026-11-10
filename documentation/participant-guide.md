# PROJECT: BLACKOUT — PARTICIPANT FIELD MANUAL

**WEB EXPLOITATION CTF 2026**  
*"Every system has a weakness. Find yours."*

---

## Welcome, Operator
You have been authorized to conduct a realistic penetration assessment against the enterprise web perimeter of **E CORP**.

### Mission Objectives
1. **Reconnaissance:** Investigate the target website starting at the root landing URL provided by the organizer (`http://TARGET-IP/`).
2. **Analysis:** Inspect source code, client scripts, HTTP headers, cookies, API endpoints, and system parameters.
3. **Exploitation:** Discover and exploit web weaknesses to recover embedded proof-of-compromise flags.
4. **Flag Capture:** Recover all 20 unique flags matching the official flag format.

---

## Flag Format
All flags in this competition adhere to the exact format:
```
Flagfound{...}
```
Example: `Flagfound{sample_flag_token_123}`

---

## Rules of Engagement
- **Authorized Scope:** Testing is restricted strictly to the designated target website and application endpoints.
- **No Host Attacks:** Do not attempt denial-of-service (DoS) attacks, aggressive fuzzing exceeding reasonable bounds, or attacks against the host hypervisor / operating system.
- **Fair Play:** Conduct penetration testing ethically according to competition guidelines.

---

## Participant Workflow
1. Receive the **Target URL** (e.g. `http://TARGET-IP/`) from competition staff.
2. Open `http://TARGET-IP/` in your browser. The page will automatically initialize into the E Corp corporate portal via `index.html`.
3. Investigate the corporate website naturally:
   - Examine page structures and HTML comments.
   - Analyze robots.txt and directory paths.
   - Review cookies, storage, and client JavaScript bundles.
   - Test search, document, feedback, and authentication workflows.
   - Perform API endpoint discovery and query parameter analysis.
4. Exploit identified vulnerabilities across Easy, Medium, Hard, and Advanced challenge tiers to extract flags.
5. Record recovered flags for submission to your competition organizer.

Good hunting.
