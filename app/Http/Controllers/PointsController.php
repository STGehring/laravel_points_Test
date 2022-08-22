<?php

namespace App\Http\Controllers;

use App\Models\Point;
use Illuminate\Http\Request;
use DB;

class PointsController extends Controller
{
    public function index($id = null)
    {
        // Ternary: if id passed, grab specific record, else all records
        $data = (is_null($id)) ? DB::table('points')->get() : Point::find($id);

        return response()->json(compact('data'));
    }


    public function getNearbyPoints(Request $request) {
        $request->validate([
            'id'          => 'required',
            'x'         => 'required|numeric',
            'y'        => 'required|numeric',
        ]);

        $id = $request->query('id');
        $x = $request->query('x');
        $y = $request->query('y');

        // I know this query's beautification is gross, I apologize.
        $data = DB::select(DB::raw("
        SELECT
            x.name,
            x.x,
            x.y,
            ROUND(x.distance, 1) as distance
        FROM 
            (
            SELECT 
                *, 
                    SQRT(
                    POWER(x - $x, 2) + POWER(y - $y, 2)
                    ):: numeric
                AS distance 
            FROM 
                points
            ) AS x 
        WHERE
            x.id != $id
            AND
            x.distance = (
            SELECT 
                MIN(
                    SQRT(
                        POWER(x - $x, 2) + POWER(y - $y, 2)
                    ):: numeric
                ) 
            FROM 
                points
            AS y
            WHERE
                y.id != $id
        )
        "));
        return $data;
    }

    public function getFarPoints(Request $request) {
        $request->validate([
            'id'          => 'required',
            'x'         => 'required|numeric',
            'y'        => 'required|numeric',
        ]);

        $id = $request->query('id');
        $x = $request->query('x');
        $y = $request->query('y');

        // I know this query's beautification is gross, I apologize.
        $data = DB::select(DB::raw("
        SELECT 
            x.name,
            x.x,
            x.y,
            ROUND(x.distance, 1) as distance
        FROM 
            (
            SELECT 
                *,
                    SQRT(
                    POWER(x - $x, 2) + POWER(y - $y, 2)
                    ):: numeric
                AS distance 
            FROM 
                points
            ) AS x 
        WHERE
            x.id != $id
            AND
            x.distance = (
            SELECT 
                MAX(
                    SQRT(
                        POWER(x - $x, 2) + POWER(y - $y, 2)
                    ):: numeric
                ) 
            FROM 
                points
            AS y
            WHERE
                y.id != $id
        )
        "));
        return $data;
    }

    public function getRelatedPoints(Request $request) {
        $request->validate([
            'id'          => 'required',
            'x'         => 'required|numeric',
            'y'        => 'required|numeric',
        ]);

        // This avoids us needing to use 2 queries on the front end
        $data['far'] = $this->getFarPoints($request);
        $data['close'] = $this->getNearbyPoints($request);

        return response()->json(compact('data'));
    }


    public function create(Request $request, $id = null)
    {
        $request->validate([
            'name'          => 'required',
            'x'         => 'required|numeric',
            'y'        => 'required|numeric',
        ]);

        // Allows us to route our edit and create to the same place, yay!
        Point::updateOrCreate(
            [
                'id' => $id
            ],
            [
                'name' => $request->name,
                'x' => $request->x,
                'y' => $request->y,
            ]
        );

        return response()->json([ 'success' => true ]);
    }


    public function destroy(Request $request, $id)
    {
        Point::destroy($id);
        return response()->json([ 'success' => true ]);
    }
}
