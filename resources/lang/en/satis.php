<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'not_found' => 'Satis config file "' . pathinfo(config('satis.config'), PATHINFO_BASENAME) . '" was not found.',

];
