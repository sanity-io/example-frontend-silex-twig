<?php
require_once __DIR__ . '/../vendor/autoload.php';

$sanity = require __DIR__ . '/sanity.php';

$app = new Silex\Application();
$app['debug'] = getenv('APP_ENV') !== 'production';
$app->register(new Silex\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/../views',
]);

// Front page (movies list)
$app->get('/', function (Silex\Application $app) use ($sanity) {
    $query = '
    *[_type == "movie"] {
      _id,
      title,
      releaseDate,

      "director": crewMembers[job == "Director"][0].person->name,
      "posterUrl": poster.asset->url
    }[0...50]';

    $movies = $sanity->fetch($query);
    return $app['twig']->render('index.twig', ['movies' => $movies]);
})->bind('movies');

// Movie page
$app->get('/movie/{id}', function (Silex\Application $app, $id) use ($sanity) {
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

    $movie = $sanity->fetch($query, ['id' => $id]);
    return $app['twig']->render('movie.twig', $movie);
})->bind('movie');

// People page
$app->get('/people', function (Silex\Application $app) use ($sanity) {
    $query = '
    *[_type == "person"] {
      _id,
      name,
      "imageUrl": image.asset->url,
      "movies": *[_type == "movie" && references(^._id)] {
        _id,
        title
      }
    }[0...50]';

    $people = $sanity->fetch($query);
    return $app['twig']->render('people.twig', ['people' => $people]);
})->bind('people');

// Person page
$app->get('/person/{id}', function (Silex\Application $app, $id) use ($sanity) {
    $query = '
    *[_type == "person" && _id == $id] {
      _id,
      name,
      "imageUrl": image.asset->url,
      "actedIn": *[_type == "movie" && references(^._id) && (^._id in castMembers[].person._ref)] {
        _id,
        title,
        releaseDate,
        "posterUrl": poster.asset->url
      }
    }[0]
    ';

    $person = $sanity->fetch($query, ['id' => $id]);
    return $app['twig']->render('person.twig', $person);
})->bind('person');

$app->run();
