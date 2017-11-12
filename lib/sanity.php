<?php
// Find your project ID and dataset in `sanity.json` in your studio project,
// or in the project management console at https://manage.sanity.io/
$sanity = new Sanity\Client([
    'projectId' =>  'zp7mbokg',
    'dataset' => 'production',
    'useCdn' => true
    // useCdn == true gives fast, cheap responses using a globally distributed cache.
    // Set this to false if your application require the freshest possible data always
    // (potentially slightly slower and a bit more expensive).
]);

return $sanity;
