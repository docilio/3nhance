import sqlite3
from faker import Faker
import random

DB_PATH = "demo/user_data.db"

def create_db_with_gaps():
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()

    # Drop table if exists (optional, for re-run)
    cursor.execute("DROP TABLE IF EXISTS users")

    # Create users table
    cursor.execute("""
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            email TEXT UNIQUE,
            city TEXT,
            job TEXT,
            nationality TEXT,
            mobile_number TEXT,
            passport TEXT UNIQUE
        )
    """)

    fake = Faker()

    users = []
    num_users = 50
    num_with_gaps = 20

    # Generate complete users first
    for _ in range(num_users):
        name = fake.name()
        email = fake.unique.email()
        city = fake.city()
        job = fake.job()
        nationality = fake.country()
        mobile_number = fake.phone_number()
        passport = fake.unique.bothify(text='??######')  # e.g., AB123456

        users.append({
            "name": name,
            "email": email,
            "city": city,
            "job": job,
            "nationality": nationality,
            "mobile_number": mobile_number,
            "passport": passport
        })

    # Randomly remove fields to create gaps in at least 20 users
    gap_fields = ["name", "email", "city", "job", "nationality", "mobile_number", "passport"]
    users_with_gaps_indices = random.sample(range(num_users), num_with_gaps)

    for idx in users_with_gaps_indices:
        # For each user with gaps, randomly remove 1 or 2 fields
        num_gaps = random.choice([1, 2])
        fields_to_remove = random.sample(gap_fields, num_gaps)
        for field in fields_to_remove:
            users[idx][field] = None

    # Insert into DB
    insert_data = [
        (
            user["name"],
            user["email"],
            user["city"],
            user["job"],
            user["nationality"],
            user["mobile_number"],
            user["passport"]
        )
        for user in users
    ]

    cursor.executemany("""
        INSERT INTO users (name, email, city, job, nationality, mobile_number, passport)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    """, insert_data)

    conn.commit()
    conn.close()
    print(f"Database created at {DB_PATH} with {num_users} users and gaps in {num_with_gaps} users")

if __name__ == "__main__":
    create_db_with_gaps()

