# Today's Work / multi-teacher demo

Load (in this order):

```bash
/Applications/XAMPP/xamppfiles/bin/mysql --socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock -u root edschool < database/seeds/today_work_demo_data.sql

/Applications/XAMPP/xamppfiles/bin/mysql --socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock -u root edschool < database/seeds/multi_teacher_diary_demo.sql
```

## Workflow to evaluate

1. **Each subject teacher** submits Student Work Report + Today's Work for **their subject only**.
2. **Class teacher** Final Submits to Principal when **all** subjects are filled.
3. Subject teachers cannot Final Submit / unlock the day.

## Teachers (First Class · A)

| Login | Password | Role on First Class A | Also |
|-------|----------|------------------------|------|
| `rakhi` | (existing) | **Class teacher** + Hindi subject | CT of class 4; **Coordinator** of Second Class B |
| `priya` | `teacher123` | English subject teacher only | **Class teacher of Second Class · B**; **Coordinator** of First Class A |
| `amit` | `teacher123` | Math subject teacher only | Not a class teacher anywhere |
| `neha` | `teacher123` | — (not subject teacher on 1A) | Hindi on Second Class B; **Moderator** of First Class A |

Assign more from Academic → Class & Section → **Assign Coordinator / Moderator** (same teacher list as class teacher).

## Seeded state for 2026-08-05 (today)

| Subject | Teacher | Status |
|---------|---------|--------|
| Hindi | Rakhi | Submitted |
| English | Priya | Submitted |
| Mathematics | Amit | **Not submitted** (waiting) |

→ Class teacher (`rakhi`) sees Final Submit disabled until Amit submits Math.

Yesterday (`2026-08-04`) is already final-submitted by Rakhi with `review_deadline = 2026-08-13` (7 working days).

## Coordinator review (7 working days)

1. Login as `priya` / `teacher123` (Coordinator of First Class A).
2. Digital Diary → **Coordinator Tasks** → Pending for `2026-08-04` / First Class A.
3. Open **Review & Submit** → mark Fair / Writing / Learning per student → **Check & Submit review**.
4. Window shows deadline / days left (or Overdue). After submit, marks lock.
5. `neha` (Moderator of 1A) can use the same review path.

## Where to click

1. Login as `amit` / `teacher123` → Digital Diary → Today's Work → date `2026-08-05`, First Class, A  
   - Only Math editable; no Final Submit button  
2. Submit SWR + Today's Work for Math  
3. Login as `rakhi` → same filters → Final Submit → Principal (sets `review_deadline` = +7 working days)  
4. Login as `priya` → Coordinator Tasks → review yesterday within the window  

Admin: `admin@edschool.com` can still Final Submit as non-teacher.
