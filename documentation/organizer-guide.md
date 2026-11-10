# PROJECT: BLACKOUT — ORGANIZER & EVENT MARSHAL GUIDE

**CONFIDENTIAL // COMPETITION STAFF ONLY**

---

## 1. Event Structure & Point Distribution

| Difficulty Level | Number of Challenges | Points Each | Total Points |
|:---|:---:|:---:|:---:|
| **Easy** | 3 | 50 | 150 |
| **Medium** | 7 | 100 | 700 |
| **Hard** | 5 | 200 | 1,000 |
| **Advanced** | 5 | 300 | 1,500 |
| **TOTAL** | **20** | — | **3,350** |

---

## 2. Master Flag Registry

| ID | Title | Difficulty | Points | Flag String |
|:---|:---|:---:|:---:|:---|
| **E01** | Ghost in the Source | Easy | 50 | `Flagfound{source_code_ghost_91}` |
| **E02** | Forbidden Path | Easy | 50 | `Flagfound{robots_path_discovered_27}` |
| **E03** | Cookie Monster | Easy | 50 | `Flagfound{cookie_monster_583}` |
| **M01** | Hidden Identity | Medium | 100 | `Flagfound{identity_exposed_402}` |
| **M02** | Parameter Manipulator | Medium | 100 | `Flagfound{parameter_master_719}` |
| **M03** | Directory Detective | Medium | 100 | `Flagfound{directory_detective_625}` |
| **M04** | JavaScript Secrets | Medium | 100 | `Flagfound{javascript_secrets_841}` |
| **M05** | Reflected Nightmare | Medium | 100 | `Flagfound{reflected_nightmare_963}` |
| **M06** | Forgotten Endpoint | Medium | 100 | `Flagfound{forgotten_endpoint_318}` |
| **M07** | Broken Login | Medium | 100 | `Flagfound{broken_login_572}` |
| **H01** | Database Reaper | Hard | 200 | `Flagfound{database_reaper_746}` |
| **H02** | Shadow Directory | Hard | 200 | `Flagfound{shadow_directory_219}` |
| **H03** | Black Mirror | Hard | 200 | `Flagfound{black_mirror_853}` |
| **H04** | Broken Crown | Hard | 200 | `Flagfound{broken_crown_691}` |
| **H05** | Dangerous Attachment | Hard | 200 | `Flagfound{dangerous_attachment_435}` |
| **A01** | Internal Affairs | Advanced | 300 | `Flagfound{internal_affairs_728}` |
| **A02** | Zero Trust | Advanced | 300 | `Flagfound{zero_trust_946}` |
| **A03** | GraphQL Mystery | Advanced | 300 | `Flagfound{graphql_mystery_512}` |
| **A04** | Race Against Time | Advanced | 300 | `Flagfound{race_condition_374}` |
| **A05** | The Phantom Admin | Advanced | 300 | `Flagfound{phantom_admin_187}` |

---

## 3. Flag Storage Architecture

Flags are strictly guarded outside the web-accessible document root:
- Primary Registry: `target-site/challenge-secrets/flags.php`
- Challenge Secret Files: `target-site/challenge-secrets/hard/H02_shadow.txt`
- Isolated Mock Service: `target-site/challenge-services/ssrf/server.py`

These files are never directly reachable through public URLs, web server directory listings, public API responses, or client-side JavaScript.

---

## 4. Challenge Walkthroughs & Reset Procedures

For full organizer walkthroughs, discovery paths, exploitation chains, test commands, and reset procedures for each challenge, refer to the private walkthrough files:
`documentation/private-walkthroughs/[ID].md` (from `E01.md` through `A05.md`).
