-- PROJECT: BLACKOUT — TARGET DATABASE SEED DATA
-- E CORP CORPORATE SYNTHETIC DATA

-- Users
INSERT INTO users (id, username, password_hash, full_name, email, role, department, clearance_level) VALUES
(1, 'johndoe', '$2y$10$w09923KJGKLDFJ09230498u/w2k3j4kljdfgklj234klj234klj23', 'John Doe', 'j.doe@ecorp.corp', 'user', 'Operations', 1),
(2, 'guest_test', '$2y$10$w09923KJGKLDFJ09230498u/w2k3j4kljdfgklj234klj234klj23', 'Guest Employee', 'guest@ecorp.corp', 'user', 'Contractor', 1),
(3, 'alden_cross', '$2y$10$w09923KJGKLDFJ09230498u/w2k3j4kljdfgklj234klj234klj23', 'Alden Cross', 'a.cross@ecorp.corp', 'manager', 'Engineering', 2),
(4, 'system_operator', '$2y$10$w09923KJGKLDFJ09230498u/w2k3j4kljdfgklj234klj234klj23', 'Operator Root', 'operator@ecorp.corp', 'admin', 'SecOps', 5);

-- Employees (Used for M01 IDOR and corporate profiles)
INSERT INTO employees (id, user_id, employee_code, first_name, last_name, department, title, email, phone, badge_id, profile_bio, clearance_level, salary_tier) VALUES
(1, 1, 'EC-1001', 'John', 'Doe', 'Customer Operations', 'Support Engineer', 'j.doe@ecorp.corp', '+1-555-0192', 'BDG-1001', 'Specializes in level 1 enterprise support and system monitoring.', 1, 'T2'),
(2, 2, 'EC-1002', 'Sarah', 'Connor', 'Quality Assurance', 'Systems Analyst', 's.connor@ecorp.corp', '+1-555-0193', 'BDG-1002', 'Lead QA engineer focusing on automation pipelines.', 1, 'T2'),
(3, 3, 'EC-1003', 'Alden', 'Cross', 'Engineering', 'Infrastructure Director', 'a.cross@ecorp.corp', '+1-555-0194', 'BDG-1003', 'Directs corporate cloud infrastructure and edge deployments.', 3, 'T4'),
(4, NULL, 'EC-1004', 'Elena', 'Rostova', 'Finance', 'Senior Comptroller', 'e.rostova@ecorp.corp', '+1-555-0195', 'BDG-1004', 'Audits global transaction pipelines and corporate expense accounts.', 2, 'T3'),
(7, NULL, 'EC-7001', 'Devon', 'Miles', 'Security Operations', 'SOC Team Lead', 'd.miles@ecorp.corp', '+1-555-0198', 'BDG-7001', 'Maintains 24/7 security monitoring across corporate datacenter assets.', 3, 'T4'),
-- M01 IDOR TARGET RECORD (Accessible by requesting id=99)
(99, NULL, 'EC-9901', 'Alexander', 'Vance', 'Executive Security Council', 'Chief Security Architect', 'a.vance@ecorp.corp', '+1-555-0999', 'BDG-EXEC-99', 'RESTRICTED EXECUTIVE CLEARANCE: Assigned to Project Blackout Security Review. Clearance Token: Flagfound{identity_exposed_402} - Note: Audit all profile endpoints for missing ownership validations.', 5, 'T6');

-- Customers
INSERT INTO customers (id, account_number, company_name, contact_name, email, tier, contract_status) VALUES
(1, 'ACC-7001', 'Global Logistics Co.', 'Marcus Wayne', 'mwayne@globallogistics.fake', 'Enterprise Platinum', 'Active'),
(2, 'ACC-7002', 'Vanguard Data Systems', 'Lucia Mendes', 'lmendes@vanguarddata.fake', 'Enterprise Standard', 'Active'),
(3, 'ACC-7003', 'Apex Financial Group', 'Arthur Dent', 'adent@apexfin.fake', 'Enterprise Titanium', 'Review Pending');

-- Documents
INSERT INTO documents (id, title, filename, file_type, category, classification, uploaded_by, download_count) VALUES
(1, 'Corporate Overview 2026', 'corporate_overview_2026.pdf', 'PDF', 'Corporate', 'Public', 1, 142),
(2, 'Enterprise Architecture Guidelines', 'enterprise_arch_v3.pdf', 'PDF', 'Technical', 'Internal', 3, 89),
(3, 'Q1 Infrastructure Report', 'infrastructure_q1_summary.pdf', 'PDF', 'Infrastructure', 'Confidential', 3, 56),
(4, 'Incident Response Playbook (Redacted)', 'ir_playbook_redacted.pdf', 'PDF', 'Security', 'Internal', 7, 34);

-- Blog Posts (Searchable via H01 SQL Injection)
INSERT INTO blog_posts (id, slug, title, summary, content, category, author, is_published) VALUES
(1, 'future-of-enterprise-cybersecurity-2026', 'The Future of Enterprise Cybersecurity in 2026', 'E Corp continues pioneering autonomous cyber defense paradigms and quantum-resilient encryption.', 'As digital landscapes expand and adversary sophistication accelerates, traditional boundary defense paradigms are insufficient. E Corp announces our unified Zero-Trust Enterprise Fabric combining predictive anomaly heuristics and distributed policy enforcement nodes across our cloud perimeter.', 'Security', 'Alden Cross', TRUE),
(2, 'ecorp-datacenter-expansion', 'Global Datacenter Expansion: APAC Region', 'Enhancing edge compute capabilities and microsecond transaction latencies across Asian financial hubs.', 'We have completed the deployment of three new state-of-the-art carrier-neutral datacenters in Singapore, Tokyo, and Seoul. Each facility features redundant dark fiber connectivity, bio-metric security gates, and dedicated hardware security modules (HSMs).', 'Infrastructure', 'Sarah Connor', TRUE),
(3, 'autonomous-systems-whitepaper', 'Autonomous Infrastructure: Efficiency at Scale', 'How automated failover and telemetry analytics minimize enterprise service interruptions.', 'Self-healing systems reduce MTTR by over 74%. E Corp telemetry ingestion engines process over 2.5 billion daily events, isolating anomalous deviations and rerouting workloads before outages propagate.', 'Technology', 'Devon Miles', TRUE);

-- Comments (Initial comments for H03 Stored XSS)
INSERT INTO comments (id, post_id, author_name, author_email, comment_text, status) VALUES
(1, 1, 'David K.', 'david.k@partner.fake', 'Excellent perspective on proactive security posture. Looking forward to the product briefing.', 'approved'),
(2, 1, 'Maya Lin', 'mlin@fintech.fake', 'Does the Zero-Trust Fabric integrate with legacy LDAP directories?', 'approved');

-- Challenge Records (Used in H01 SQL injection query targets)
INSERT INTO challenge_records (id, challenge_id, record_key, secret_value, description, flag_content) VALUES
(1, 'H01', 'DB_SECRET_REAPER_VAULT', 'RESTRICTED_QUERY_EXTRACT_KEY', 'SQL Injection verification token from isolated challenge table', 'Flagfound{database_reaper_746}');

-- Rewards Table (Used for A04 Race condition)
INSERT INTO rewards (id, user_id, credits, coupons_claimed, vip_status) VALUES
(1, 1, 10, 0, 'Standard'),
(2, 2, 10, 0, 'Standard');

-- Admin Audit Logs (Used for A02 / A05)
INSERT INTO admin_audit_logs (id, actor, action, resource, ip_address) VALUES
(1, 'system', 'SERVICE_STARTUP', 'Core Engine', '127.0.0.1'),
(2, 'operator_root', 'POLICY_UPDATE', '/api/admin/audit.php', '10.0.0.5'),
(3, 'security_scanner', 'PORT_AUDIT', 'Internal Gateway', '127.0.0.1');
