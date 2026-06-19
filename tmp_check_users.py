import psycopg2

conn = psycopg2.connect(host='127.0.0.1', port=5433, dbname='Tugas', user='postgres', password='Anakpreman123')
cur = conn.cursor()
cur.execute("""
select u.id, u.name, u.email, coalesce(string_agg(r.name, ',' order by r.name), '')
from users u
left join role_user ru on ru.user_id = u.id
left join roles r on r.id = ru.role_id
group by u.id, u.name, u.email
order by u.id
""")
for row in cur.fetchall():
    print(row)
conn.close()
