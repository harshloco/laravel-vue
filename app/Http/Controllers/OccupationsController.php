<?php

namespace App\Http\Controllers;

use App\Contracts\OccupationParser;
use App\Handlers\OccupationHandler;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OccupationsController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $occparser;
    /**
     * @var OccupationHandler
     */
    private $handler;

    public function __construct(OccupationParser $parser, OccupationHandler $handler)
    {
        $this->occparser = $parser;
        $this->handler = $handler;
    }

    public function index()
    {
        return $this->occparser->list();
    }

    public function compare(Request $request)
    {
        $this->occparser->setScope('skills');

        $occupation_1 = $this->occparser->get($request->get('occupation_1'));
        $occupation_2 = $this->occparser->get($request->get('occupation_2'));

        if($occupation_1 && $occupation_2) {

            $result = $this->handler->getCompareResult($occupation_1, $occupation_2);

            return [
                'match' => $result[0],
                'result' => $result[1]
            ];
        }

        return [
            'match' => 0,
            'result' => []
        ];
    }
}
