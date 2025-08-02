import csv
from faker import Faker
import random
import os

CSV_PATH = "demo/user_data.csv"

def create_csv_with_gaps():
    fake = Faker()
    num_users = 50
    num_with_gaps = 20
    gap_fields = ["name", "email",  "city", "job", "nationality", "mobile_number", "passport"]

    users = []
    for _ in range(num_users):
        user = {
            "name": fake.name(),
            "email": fake.unique.email(),
            "city": fake.city(),
            "job": fake.job(),
            "nationality": fake.country(),
            "mobile_number": fake.phone_number(),
            "passport": fake.unique.bothify(text="??######")
        }
        users.append(user)

    # Introduce missing data
    indices_with_gaps = random.sample(range(num_users), num_with_gaps)
    for idx in indices_with_gaps:
        num_gaps = random.choice([1, 2])
        fields_to_null = random.sample(gap_fields, num_gaps)
        for field in fields_to_null:
            users[idx][field] = ""  # Empty string = missing value in CSV

    # Write to CSV
    os.makedirs(os.path.dirname(CSV_PATH), exist_ok=True)
    with open(CSV_PATH, mode="w", newline="", encoding="utf-8") as csvfile:
        fieldnames = gap_fields
        writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(users)

    print(f"CSV created at {CSV_PATH} with {num_users} users and {num_with_gaps} users containing gaps")

if __name__ == "__main__":
    create_csv_with_gaps()

