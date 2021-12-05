# Apostolos

[![codecov](https://codecov.io/gh/StephanMeijer/Apostolos/branch/master/graph/badge.svg?token=X4DZUUYX3F)](https://codecov.io/gh/StephanMeijer/Apostolos)

![](https://www.kerknet.be/sites/default/files/styles/width_640/public/F2184g39_0.jpg)

> Whatever you do, work at it with all your heart.

**- Apostole Paul**

Also remember: **Sigmund Freud** says in _Civilization and Its Discontents_ that man needs two things for happiness: _“Love and work, work and love."_

## Ok what is this bullshit exactly?

Just a tool to use to track time in ical feeds. You can now track time in Google Calendar.

## And how do I use it?

Example config: `~/.apostolos.yml`:

```yml
calendars:
  - name: Client1
    url: "https://calendar.google.com/calendar/ical/692cf04a-1d49-4772-b09b-0ec324853277/basic.ics"
    rate: 25
  - name: Client2
    url: "https://calendar.google.com/calendar/ical/0f26df45-f42e-4e56-b495-67cb11f5ea91/basic.ics"
    rate: 105
```

And then run:

```
$ bin/console time:summary Client1 --year 2021 --month february
```

## Output formats

### Normal / CLI

Default this output is in use. Example:

```
+--- Hours of 2021-12 ---+
| Day (start) | Duration |
+-------------+----------+
| 04-12-2021  | 03:53    |
| 07-12-2021  | 02:30    |
| 23-12-2021  | 10:00    |
+----- Total: 16:23 -----+
```

### JSON

Option: `-f json` or `--format json`

Example:

```json
{
    "meta": {
        "year": 2021,
        "month": 12,
        "duration": {
            "hours": 16,
            "minutes": 23
        }
    },
    "records": [
        {
            "day": "2021-12-04",
            "duration": {
                "hours": 3,
                "minutes": 53
            }
        },
        {
            "day": "2021-12-23",
            "duration": {
                "hours": 10,
                "minutes": 0
            }
        },
        {
            "day": "2021-12-07",
            "duration": {
                "hours": 2,
                "minutes": 30
            }
        }
    ]
}
```

## What license?

GPLv3.0
