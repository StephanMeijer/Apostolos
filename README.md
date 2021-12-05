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

## What license?

GPLv3.0