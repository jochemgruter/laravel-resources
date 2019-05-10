<?php
/**
 * Created by PhpStorm.
 * User: Jochem
 * Date: 13-12-2017
 * Time: 13:32
 */

namespace Gruter\ResourceViewer\Http\ViewComposers;

use App\Models\Offer;
use Gate;
use Illuminate\View\View;

class ResourceComposer
{
    public function compose(View $view)
    {
        $view->with('layout', config('resource-viewer.layout.view'));
        $view->with('section', config('resource-viewer.layout.section'));
    }

}