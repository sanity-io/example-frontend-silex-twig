<?php
require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = getenv('APP_ENV') !== 'production';
$app['config'] = require __DIR__ . '/../config/config.php';

$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/../views',
]);
$app->register(new Sanity\Silex\ServiceProvider(), [
    'sanity.client.options' => $app['config']['sanity']
]);

// Front page (movies list)
$app->get('/', function (Silex\Application $app) {
    $query = '
    *[_type == "movie"] {
      _id,
      title,
      releaseDate,

      "director": crewMembers[job == "Director"][0].person->name,
      "posterUrl": poster.asset->url
    }[0...50]';

    $movies = $app['sanity.client']->fetch($query);
    return $app['twig']->render('index.twig', ['movies' => $movies]);
})->bind('movies');

// Movie page
$app->get('/movie/{id}', function (Silex\Application $app, $id) {
    $query = '
    *[_type == "movie" && _id == $id] {
      _id,
      title,
      releaseDate,
      "posterUrl": poster.asset->url,
      "cast": castMembers[] {
        characterName,
        "person": person-> {
          _id,
          name,
          "imageUrl": image.asset->url
        }
      }
    }[0]';

    $movie = $app['sanity.client']->fetch($query, ['id' => $id]);
    return $app['twig']->render('movie.twig', $movie);
})->bind('movie');

// People page
$app->get('/people', function (Silex\Application $app) {
    $query = '
    *[_type == "person"] {
      _id,
      name,
      "imageUrl": image.asset->url
    }[0...50]';

    $people = $app['sanity.client']->fetch($query);
    return $app['twig']->render('people.twig', ['people' => $people]);
})->bind('people');

// Person page
$app->get('/person/{id}', function (Silex\Application $app, $id) {
    $query = '
    *[_type == "person" && _id == $id] {
      _id,
      name,
      "imageUrl": image.asset->url,
      "actedIn": *[_type == "movie" && references(^._id)] {
        _id,
        title,
        releaseDate,
        "posterUrl": post.asset->url
      }
    }[0]
    ';

    $person = $app['sanity.client']->fetch($query, ['id' => $id]);
    return $app['twig']->render('person.twig', $person);
})->bind('person');

$app->run();
