<?php

namespace App\Http\Middleware;

use Closure;

class Jsonp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $call_back = $request->callback;

        $return_data =  $next($request);

        if (!empty($call_back)){

            $return_data->setContent('/**/'.$call_back.'('.$return_data->content().')')->header('Content-Type', 'application/json');

            return $return_data;
        }
        //return $return_data->header('Content-Type', 'application/json');
        return $return_data;
    }
}
