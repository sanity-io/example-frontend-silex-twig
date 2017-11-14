<?php
return [
    'sanity' => [
        'projectId' =>  'zp7mbokg',
        'dataset' => 'production',
        'useCdn' => true
        // useCdn == true gives fast, cheap responses using a globally distributed cache.
        // Set this to false if your application require the freshest possible data always
        // (potentially slightly slower and a bit more expensive).
    ],
];
