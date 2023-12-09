<?php

declare(strict_types=1);

namespace MuriloChianfa\LaravelIoncubeHelper\Exceptions;

use Exception;

/**
 * LicenseNotReadableException class...
 *
 * @since 0.1.0
 * @version 1.0.0
 * @author Murilo Chianfa <github.com/MuriloChianfa>
 * @package Exceptions
 *
 * @method render
 *
 * @final
 */
final class LicenseNotReadableException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->view('vendor.license', [
            'title' => 'Without permission',
            'subtitle' => 'No read permission in the license...'
        ]);
    }
}
