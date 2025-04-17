import random
from faker import Faker

fake = Faker()

# Constants
num_entries = 1500
campus_options = ['Main', 'Hit', 'Station', 'Harar']
sex_options = ['Male', 'Female']
# List of all departments in Haramaya University
department_options = [
    # College of Agriculture and Environmental Sciences (CAES)
    "Animal and Range Science",
    "Natural Resources and Environmental Science",
    "Plant Sciences",
    "Agricultural Economics and Agribusiness",
    "Rural Development and Agricultural Extension",

    # College of Business and Economics (CBE)
    "Accounting",
    "Cooperatives",
    "Management",
    "Economics",
    "Public Administration and Development Management",

    # College of Computing and Informatics
    "Computer Science",
    "Information Science",
    "Information Technology",
    "Software Engineering",
    "Statistics",

    # College of Education and Behavioral Sciences
    "Pedagogy",
    "Special Needs",
    "Educational Planning and Management",
    "English Language Improvement Centre",

    # College of Health and Medical Sciences
    "Medicine",
    "Pharmacy",
    "Nursing and Midwifery",
    "Public Health",
    "Environmental Health Sciences",
    "Medical Laboratory Science",

    # College of Law
    "Law",

    # College of Natural and Computational Sciences
    "Biology",
    "Chemistry",
    "Mathematics",
    "Physics",

    # College of Social Sciences and Humanities
    "Afan Oromo, Literature and Communication",
    "Gender and Development Studies",
    "Foreign Languages and Journalism",
    "History and Heritage Management",
    "Geography and Environmental Studies",
    "Sociology",

    # College of Veterinary Medicine
    "Veterinary Medicine",
    "Veterinary Laboratory Technology",

    # Haramaya Institute of Technology
    "Agricultural Engineering",
    "Water Resources and Irrigation Engineering",
    "Civil Engineering",
    "Electrical and Computer Engineering",
    "Mechanical Engineering",
    "Chemical Engineering",
    "Food Science and Post-Harvest Technology",
    "Food Technology and Process Engineering",

    # Sport Sciences Academy
    "Sport Sciences",

    # College of Agro-Industry and Land Resources
    "Land Administration",
    "Dairy and Meat Technology",
    "Forest Resource Management",
    "Soil Resources and Watershed Management"
]

years = list(range(1, 8))

# Generate SQL VALUES
values_list = []

for _ in range(num_entries):
    name = fake.name().replace("'", "''")
    sex = random.choice(sex_options)
    id_number = fake.unique.random_int(min=100000, max=999999)
    department = random.choice(department_options)  # Randomly select a department from the list
    campus = random.choice(campus_options)
    pc_serial = fake.unique.bothify(text='PC-#####')
    pc_model = fake.bothify(text='Model-???##')
    contact = fake.phone_number()
    photo = ""
    year = random.choice(years)

    values = f"('{name}', '{sex}', {id_number}, '{department}', '{campus}', '{pc_serial}', '{pc_model}', '{contact}', '{photo}', {year})"
    values_list.append(values)

# Combine into one INSERT statement
sql_insert = "INSERT INTO students (name, sex, idNumber, department, campus, pcSerialNumber, pcModel, contact, photo, year) VALUES\n"
sql_insert += ",\n".join(values_list) + ";"

# Save to file or print
with open("insert_students_bulk.sql", "w") as f:
    f.write(sql_insert)

print(f"✔ SQL file with {num_entries} VALUES generated: insert_students_bulk.sql")
