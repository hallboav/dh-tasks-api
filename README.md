## DHTDL - Docler Holding To Do List

A simple TODO list.

#### Runarounds

Despite not having much time to work on this I tried to pay attention to the inputs and outputs of the API.

I believe that all code written by us needs to be tested and less code to maintain is better.\
BTW, I didn't write any test for some simple classes in `src/` of this repository, but you can always see a
public unit test that I wrote:\
https://github.com/symfony/http-client/blob/master/Tests/NoPrivateNetworkHttpClientTest.php

I chose MariaDB because I think you guys at Docler Holding make use of it.

#### Features

This API is built on top of API Platform and Symfony.

> API Platform is the most advanced API platform, in any framework or language.\
> — Fabien Potencier (creator of Symfony), SymfonyCon 2017

There is tasks and tasklists.\
Every task belongs to a tasklist, while a tasklist can have one or more tasks.

Isn't possible to create a already-done task.

All tasks are deleted when their tasklist is removed.

You can filter tasks by _done_ status.

Tasks are ordered (GET /tasks) by their creation date, while tasklists (GET /tasklists) are ordered by their updated date.

Whenever a task is updated from _todo_ to _done_, a new message is enqueued in RabbitMQ for further processing.
The same application is processing the queue (doing nothing), but it could be other system.

#### Cloning
```shell
git clone https://github.com/hallboav/dh-tasks-api.git dhtdl
cd dhtdl
```

#### Raising containers
```shell
docker-compose -f docker-compose.dev.yml up -d
docker-compose -f docker-compose.dev.yml exec php_fpm_dev composer install
docker-compose -f docker-compose.dev.yml exec php_fpm_dev bin/console doctrine:schema:create
docker-compose -f docker-compose.dev.yml exec php_fpm_dev bin/console hautelook:fixtures:load --no-interaction
```

Then you should have access to the development entrypoint:\
http://localhost:8008

#### Executing functional tests
```shell
docker-compose -f docker-compose.dev.yml exec php_fpm_dev bin/phpunit
```

#### How to run (prodution mode)
```shell
docker-compose up -d
```

Then you should have access to the entrypoint:\
http://localhost:8000

The entrypoint can be read by machines too:\
http://localhost:8000/index.jsonld

#### Test it by hand with cURL
Note: [`jq`](https://stedolan.github.io/jq/download/) is a JSON processor to help us analyse JSON strings.

Create a brand new task list:
```shell
RESPONSE=`curl -sX POST \
    http://localhost:8000/tasklists \
    -H "accept: application/ld+json" \
    -H "content-type: application/ld+json" \
    -d '{"title":"Things my wife wanst me to do"}'`
echo $RESPONSE | jq
```

Output:
```json
{
  "@context": "/contexts/Tasklist",
  "@id": "/tasklists/86610570-c5a1-11ea-a5a0-0242c0a8c002",
  "@type": "Tasklist",
  "id": "86610570-c5a1-11ea-a5a0-0242c0a8c002",
  "createdAt": "2020-07-14T09:13:32+0200",
  "updatedAt": "2020-07-14T09:13:32+0200",
  "title": "Things my wife wanst me to do",
  "tasks": []
}
```


Oh, It should be `wants`, not `wanst`. Let's rename it:
```shell
TLID=`echo $RESPONSE | jq -r '.id'`
RESPONSE=`curl -sX PATCH \
    http://localhost:8000/tasklists/$TLID \
    -H "accept: application/ld+json" \
    -H "content-type: application/merge-patch+json" \
    -d '{"title":"Things my wife wants me to do"}'`
echo $RESPONSE | jq
```

Output:
```json
{
  "@context": "/contexts/Tasklist",
  "@id": "/tasklists/86610570-c5a1-11ea-a5a0-0242c0a8c002",
  "@type": "Tasklist",
  "id": "86610570-c5a1-11ea-a5a0-0242c0a8c002",
  "createdAt": "2020-07-14T09:13:32+0200",
  "updatedAt": "2020-07-14T09:13:57+0200",
  "title": "Things my wife wants me to do",
  "tasks": []
}
```


Ok. Let's add two new tasks into it:
```shell
IRI=`echo $RESPONSE | jq -r '."@id"'`
curl -sX POST \
    http://localhost:8000/tasks \
    -H "accept: application/ld+json" \
    -H "content-type: application/ld+json" \
    -d '{"done":true,"title":"Buy a new television","details":"At least 40 inches","tasklist":"'$IRI'"}' \
| jq
```

Output:
```json
{
  "@context": "/contexts/Task",
  "@id": "/tasks/12cc6f15-c5a2-11ea-a5a0-0242c0a8c002",
  "@type": "Task",
  "id": "12cc6f15-c5a2-11ea-a5a0-0242c0a8c002",
  "createdAt": "2020-07-14T09:17:27+0200",
  "updatedAt": "2020-07-14T09:17:27+0200",
  "done": false,
  "title": "Buy a new television",
  "details": "At least 40 inches",
  "tasklist": {
    "@id": "/tasklists/86610570-c5a1-11ea-a5a0-0242c0a8c002",
    "@type": "Tasklist",
    "id": "86610570-c5a1-11ea-a5a0-0242c0a8c002",
    "createdAt": "2020-07-14T09:13:32+0200",
    "updatedAt": "2020-07-14T09:13:57+0200",
    "title": "Things my wife wants me to do"
  }
}
```


Did you noticed that you can't create tasks already done?

```shell
RESPONSE=`curl -sX POST \
    http://localhost:8000/tasks \
    -H "accept: application/ld+json" \
    -H "content-type: application/ld+json" \
    -d '{"title":"Drill wall to put curtains","tasklist":"'$IRI'"}'`
echo $RESPONSE | jq
```

Output:
```json
{
  "@context": "/contexts/Task",
  "@id": "/tasks/3a694ad2-c5a2-11ea-a5a0-0242c0a8c002",
  "@type": "Task",
  "id": "3a694ad2-c5a2-11ea-a5a0-0242c0a8c002",
  "createdAt": "2020-07-14T09:18:34+0200",
  "updatedAt": "2020-07-14T09:18:34+0200",
  "done": false,
  "title": "Drill wall to put curtains",
  "tasklist": {
    "@id": "/tasklists/86610570-c5a1-11ea-a5a0-0242c0a8c002",
    "@type": "Tasklist",
    "id": "86610570-c5a1-11ea-a5a0-0242c0a8c002",
    "createdAt": "2020-07-14T09:13:32+0200",
    "updatedAt": "2020-07-14T09:13:57+0200",
    "title": "Things my wife wants me to do"
  }
}
```


Now, let's say you want to update the done status of the second task:
```shell
ID=`echo $RESPONSE | jq -r '.id'`
curl -sX PATCH \
    http://localhost:8000/tasks/$ID \
    -H "accept: application/ld+json" \
    -H "content-type: application/merge-patch+json" \
    -d '{"done":true}' \
| jq
```

Output:
```json
{
  "@context": "/contexts/Task",
  "@id": "/tasks/3a694ad2-c5a2-11ea-a5a0-0242c0a8c002",
  "@type": "Task",
  "id": "3a694ad2-c5a2-11ea-a5a0-0242c0a8c002",
  "createdAt": "2020-07-14T09:18:34+0200",
  "updatedAt": "2020-07-14T09:19:15+0200",
  "done": true,
  "title": "Drill wall to put curtains",
  "tasklist": {
    "@id": "/tasklists/86610570-c5a1-11ea-a5a0-0242c0a8c002",
    "@type": "Tasklist",
    "id": "86610570-c5a1-11ea-a5a0-0242c0a8c002",
    "createdAt": "2020-07-14T09:13:32+0200",
    "updatedAt": "2020-07-14T09:13:57+0200",
    "title": "Things my wife wants me to do"
  }
}
```


How many tasks you have done so far?
```shell
curl -sX GET http://localhost:8000/tasks?done=true -H "accept: application/ld+json" | jq
```

Output:
```json
{
  "@context": "/contexts/Task",
  "@id": "/tasks",
  "@type": "hydra:Collection",
  "hydra:member": [
    {
      "@id": "/tasks/3a694ad2-c5a2-11ea-a5a0-0242c0a8c002",
      "@type": "Task",
      "id": "3a694ad2-c5a2-11ea-a5a0-0242c0a8c002",
      "createdAt": "2020-07-14T09:18:34+0200",
      "updatedAt": "2020-07-14T09:19:15+0200",
      "done": true,
      "title": "Drill wall to put curtains",
      "tasklist": {
        "@id": "/tasklists/86610570-c5a1-11ea-a5a0-0242c0a8c002",
        "@type": "Tasklist",
        "id": "86610570-c5a1-11ea-a5a0-0242c0a8c002",
        "createdAt": "2020-07-14T09:13:32+0200",
        "updatedAt": "2020-07-14T09:13:57+0200",
        "title": "Things my wife wants me to do"
      }
    }
  ],
  "hydra:totalItems": 1,
  "hydra:view": {
    "@id": "/tasks?done=true",
    "@type": "hydra:PartialCollectionView"
  },
  "hydra:search": {
    "@type": "hydra:IriTemplate",
    "hydra:template": "/tasks{?done}",
    "hydra:variableRepresentation": "BasicRepresentation",
    "hydra:mapping": [
      {
        "@type": "IriTemplateMapping",
        "variable": "done",
        "property": "done",
        "required": false
      }
    ]
  }
}
```


Too verbose? Just say that you accept raw JSON.
```shell
curl -sX GET http://localhost:8000/tasks?done=true -H "accept: application/json" | jq
```

Output:
```json
[
  {
    "id": "3a694ad2-c5a2-11ea-a5a0-0242c0a8c002",
    "createdAt": "2020-07-14T09:18:34+0200",
    "updatedAt": "2020-07-14T09:19:15+0200",
    "done": true,
    "title": "Drill wall to put curtains",
    "tasklist": {
      "id": "86610570-c5a1-11ea-a5a0-0242c0a8c002",
      "createdAt": "2020-07-14T09:13:32+0200",
      "updatedAt": "2020-07-14T09:13:57+0200",
      "title": "Things my wife wants me to do"
    }
  }
]
```


What if you want to remove the tasklist?
```shell
curl -X DELETE http://localhost:8000/tasklists/$TLID -H "accept: */*"
```

Output:
```
```


#### API Reference

##### Tasklist

| Endpoint                   | Method | Meaning    |
| -------------------------- | ------ | ---------- |
| `/tasklists`               | GET    | List all   |
| `/tasklists`               | POST   | Create new |
| `/tasklists/{id}`          | DELETE | Remove     |
| `/tasklists/{id}`          | PATCH  | Rename     |

##### Task

| Endpoint                    | Method | Meaning                                    |
| --------------------------- | ------ | ------------------------------------------ |
| `/tasks{?done=true\|false}` | GET    | List all                                   |
| `/tasks`                    | POST   | Create new                                 |
| `/tasks/{id}`               | DELETE | Remove                                     |
| `/tasks/{id}`               | PATCH  | Rename, modify details and/or mark as done |
