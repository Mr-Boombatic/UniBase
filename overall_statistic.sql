# Количество проектов
SELECT COUNT(*) AS total_projects
FROM project;

# Количество сотрудников
SELECT COUNT(*) AS total_workers
FROM worker;

# Количество закрытых проектов
SELECT COUNT(*) AS closed_projects
FROM project
WHERE is_closed = 1;

# Количество проектов по каждому заказчику
SELECT customer, COUNT(*) AS total_projects
FROM project
GROUP BY customer;

# Список работников с их проектами
SELECT w.fullname, p.name AS project_name
FROM worker w
         JOIN project_worker pw ON w.id = pw.worker_id
         JOIN project p ON pw.project_id = p.id;

# Проекты с количеством работников в каждом проекте
SELECT p.name AS project_name, COUNT(pw.worker_id) AS number_of_workers
FROM project p
         LEFT JOIN project_worker pw ON p.id = pw.project_id
GROUP BY p.id;

# Проекты с количеством работников в каждом проекте
SELECT p.name AS project_name, COUNT(pw.worker_id) AS number_of_workers
FROM project p
LEFT JOIN project_worker pw ON p.id = pw.project_id
GROUP BY p.id;

# Средний возраст сотрудников
SELECT AVG(TIMESTAMPDIFF(YEAR, birthdate, CURDATE())) AS average_age
FROM worker;