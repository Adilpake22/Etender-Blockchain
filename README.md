# 🏛️ eTender — Blockchain-Simulated Government Tender Portal

A secure, role-based e-tendering web application built with **PHP + MySQL**, featuring a **commit-reveal bidding scheme** and a **blockchain-simulated audit trail** — no Node.js or external blockchain required.

---

## 📌 Features

- **Role-based access control** — Admin, Evaluator, and Bidder roles
- **Commit-reveal bidding** — Bids are hashed with a secret PIN before submission, preventing bid peeking
- **Blockchain simulation** — Every action (tender creation, bid commit, reveal, award) generates a realistic Ethereum-style `tx_hash` and is logged to an immutable audit trail
- **Tender lifecycle management** — Create → Open → Close → Evaluate → Award
- **Transparent audit log** — Publicly viewable transaction history for every tender and bid
- **Scoring system** — Evaluators score bids on Technical (40%) + Financial (60%) criteria

---

## 🧱 Tech Stack

| Layer      | Technology              |
|------------|-------------------------|
| Backend    | PHP 8.x (no framework)  |
| Database   | MySQL 8.x               |
| Frontend   | Bootstrap 5, vanilla JS |
| Blockchain | Pure PHP simulation (SHA-256 hashing + audit log) |

---

## 🗂️ Project Structure

```
etender/
├── index.php                    # Entry point — redirects by role
├── api/
│   ├── auth/
│   │   ├── login.php
│   │   ├── logout.php
│   │   └── register.php
│   ├── bid/
│   │   ├── commit.php           # Submit hashed bid
│   │   ├── reveal.php           # Reveal bid with PIN
│   │   └── list.php
│   ├── tender/
│   │   ├── create.php
│   │   ├── close.php
│   │   └── list.php
│   └── evaluation/
│       ├── score.php            # Score a revealed bid
│       └── award.php            # Award tender to winner
├── app/
│   ├── config/
│   │   └── database.php         # PDO connection setup
│   ├── helpers/
│   │   ├── Auth.php             # Session-based auth helpers
│   │   └── Blockchain.php       # TX hash generation + audit logging
│   └── models/
│       ├── User.php
│       ├── Tender.php
│       └── Bid.php
├── views/
│   ├── layouts/
│   │   ├── navbar.php
│   │   └── footer.php
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── create_tender.php
│   │   └── evaluate.php
│   ├── bidder/
│   │   ├── dashboard.php
│   │   ├── tender_list.php
│   │   └── reveal_bid.php
│   └── public/
│       └── audit_trail.php
├── public/
│   └── css/
│       └── custom.css
└── database/
    └── schema.sql               # Full DB schema + demo seed data
```

---

## ⚙️ Installation

### Prerequisites

- PHP 8.0+
- MySQL 8.0+
- Apache or Nginx with `mod_rewrite` enabled
- A local web server (XAMPP / WAMP / Laragon / plain Apache)

### Steps

1. **Clone the repository**

   ```bash
   git clone https://github.com/your-username/etender.git
   cd etender
   ```

2. **Import the database**

   ```bash
   mysql -u root -p < database/schema.sql
   ```

   This creates the `etender` database and seeds demo users and tenders.

3. **Configure the database connection**

   Edit `app/config/database.php`:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'db_etender');
   define('DB_USER', 'root');
   define('DB_PASS', '');        // Set your MySQL password if needed
   ```

4. **Place in your web server root**

   Copy the `etender/` folder to your server's web root (e.g., `htdocs/` for XAMPP).

5. **Access the app**

   ```
   http://localhost/etender/
   ```

---

## 👤 Demo Credentials

All demo accounts use the password: **`password`**

| Role      | Email                          | Password   |
|-----------|--------------------------------|------------|
| Admin     | admin@etender.gov.in           | password   |
| Evaluator | evaluator@etender.gov.in       | password   |
| Bidder 1  | abc@construction.com           | password   |
| Bidder 2  | xyz@infra.com                  | password   |

---

## 🔄 Tender Lifecycle

```
Admin Creates Tender
        ↓
   Tender: OPEN
        ↓
  Bidders Submit Hashed Bids (commit phase)
        ↓
   Admin Closes Tender
        ↓
  Bidders Reveal Bids with PIN (reveal phase)
        ↓
   Evaluator Scores Bids (Technical 40% + Financial 60%)
        ↓
   Admin Awards Tender → Winner notified, others rejected
```

---

## 🔐 How Commit-Reveal Bidding Works

1. **Commit phase** — A bidder enters their bid amount and a 4-digit secret PIN. The system computes:
   ```
   bid_hash = SHA-256(amount | PIN)
   ```
   Only the hash is stored — the actual amount is hidden.

2. **Reveal phase** — After the tender is closed, the bidder re-enters their amount and PIN. The system recomputes the hash and verifies it matches the committed hash before accepting the reveal.

This prevents bid manipulation — no one (including admins) can see actual bid amounts until the reveal phase.

---

## 📊 Scoring Formula

```
Final Score = (Technical Score × 0.4) + (Financial Score × 0.6)
```

Evaluators assign scores (0–100) for both criteria. The bid with the highest final score wins.

---

## 🔗 Blockchain Simulation

The `Blockchain.php` helper simulates on-chain behavior without any external dependency:

- Generates cryptographically random **Ethereum-style transaction hashes** (`0x` + 64 hex chars) using `random_bytes(32)`
- Logs every action (tender published, bid committed, bid revealed, tender awarded) to the `audit_log` table
- The public Audit Trail page displays all logs with their `tx_hash` for transparency

---

## 🗄️ Database Schema

| Table       | Description                                      |
|-------------|--------------------------------------------------|
| `users`     | Registered users with role (admin/bidder/evaluator) |
| `tenders`   | Tender listings with status, budget, deadline    |
| `bids`      | Bid commitments, reveal data, scores             |
| `audit_log` | Immutable log of all blockchain-simulated events |

---

## 🛡️ Security Notes

- Passwords are hashed using `password_hash()` (bcrypt)
- All DB queries use **PDO prepared statements** — no raw SQL
- Sessions are used for authentication; role is checked on every sensitive API endpoint
- Bid amounts are hidden via SHA-256 hash until the reveal phase

> ⚠️ **For production use**, add CSRF protection, HTTPS, input sanitization, and replace the blockchain simulation with a real smart contract integration (e.g., Ethereum + Web3.php).

---

## 📄 License

MIT License. See [LICENSE](LICENSE) for details.






<img width="766" height="873" alt="image" src="https://github.com/user-attachments/assets/723a46b1-b7f2-4a4a-8c2d-4c271c8147d7" />
<img width="1600" height="766" alt="image" src="https://github.com/user-attachments/assets/ff9ff09e-a4c5-4219-a08d-00e165354ab0" />
<img width="1302" height="809" alt="image" src="https://github.com/user-attachments/assets/b6c9ffde-13b5-4384-a7b6-c226f2365a84" />
<img width="1600" height="699" alt="image" src="https://github.com/user-attachments/assets/440b4873-6c35-46a7-b169-91b6b22b627e" />




