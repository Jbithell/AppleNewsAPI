# AppleNews

## Install

```shell
git clone git@github.com:chapter-three/ApppleNews.git
cd AppleNews
curl -sS https://getcomposer.org/installer | php
./composer.phar install
```

## Unit Tests

```shell
./vendor/bin/phpunit -v --colors=auto --bootstrap vendor/autoload.php tests
```

## PushAPI Methods

```php
$api_key_id = "";
$api_key_secret = "";
$endpoint = "https://endpoint_url";

$PushAPI = new ChapterThree\AppleNews\PushAPI(
  $api_key_id,
  $api_key_secret,
  $endpoint
);
```

### GET Methods

##### GET Channel

```php
// Fetches information about a channel.
$response = $PushAPI->get('/channels/{channel_id}',
  [
    'channel_id' => CHANNEL_ID
  ]
);
```

##### GET Sections

```php
// Fetches a list of all sections for a channel.
$response = $PushAPI->get('/channels/{channel_id}/sections',
  [
    'channel_id' => CHANNEL_ID
  ]
);
```

##### GET Section

```php
// Fetches information about a single section.
$response = $PushAPI->get('/sections/{section_id}',
  [
    'section_id' => SECTION_ID
  ]
);
```

##### GET Article

```php
// Fetches an article.
$response = $PushAPI->get('/articles/{article_id}',
  [
    'article_id' => ARTICLE_ID
  ]
);
```

### POST Methods

##### POST Article

```php
// Publishes a new article to a channel.
$response = $PushAPI->post('/channels/{channel_id}/articles',
  [
    'channel_id' => CHANNEL_ID
  ],
  [
    // List of files to POST
    'files' => [], // not required when `json` not empty
    // JSON metadata string
    'metadata' => '', // optional
    // Submit contents of the article.json file if
    // the file isn't provied in the `files` array
    'json' => '', // optional
  ]
);
```

### DELETE Methods

##### DELETE Article

```php
// Deletes an article.
$response = $PushAPI->delete('/articles/{article_id}',
  [
    'article_id' => ARTICLE_ID
  ]
);
```
