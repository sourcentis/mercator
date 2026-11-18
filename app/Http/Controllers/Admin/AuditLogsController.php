<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Cartographer;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuditLogsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('audit_log_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', session($this->perPageSessionKey(), 100));

        // On borne les valeurs possibles pour éviter les conneries
        $allowedPerPage = [10, 25, 50, 100, 1000];
        if (! in_array($perPage, $allowedPerPage)) {
            $perPage = 100;
        }

        session()->put($this->perPageSessionKey(), $perPage);

        $query = DB::table('audit_logs')
            ->select(
                'audit_logs.id',
                'description',
                'subject_type',
                'subject_id',
                'users.name',
                'user_id',
                'host',
                'audit_logs.created_at'
            )
            ->join('users', 'users.id', '=', 'user_id');

        if (! empty($search)) {

            // Découpe la recherche en mots séparés
            $terms = preg_split('/\s+/', trim($search));

            // Pour chaque mot, on impose une condition (AND)
            foreach ($terms as $term) {
                $like = '%'.mb_strtolower($term).'%';
                $query->where(function ($q) use ($like) {
                    $q->whereRaw('LOWER(description) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(properties) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(subject_type) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(users.name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(host) LIKE ?', [$like]);
                });
            }
        }

        $logs = $query
            ->orderBy('audit_logs.id', 'desc')
            ->paginate($perPage)
            ->appends([
                'search' => $search,
                'per_page' => $perPage,
            ]);

        return view('admin.auditLogs.index', [
            'logs' => $logs,
            'search' => $search,
            'perPage' => $perPage,
            'perPageOptions' => $allowedPerPage,
        ]);
    }

    public function show(AuditLog $auditLog)
    {
        if (Gate::denies('audit_log_show')) {
            $user = auth()->user();
            $roleIds = $user->roles()->pluck('roles.id');

            $isCartographer = Cartographer::where('cartographiable_type', $auditLog->subject_type)
                ->where('cartographiable_id', $auditLog->subject_id)
                ->where(function ($q) use ($user, $roleIds) {
                    $q->where('user_id', $user->id)
                        ->orWhereIn('role_id', $roleIds);
                })
                ->exists();

            abort_if(! $isCartographer, Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        return view('admin.auditLogs.show', compact('auditLog'));
    }

    public function history(Request $request)
    {
        abort_if(($request->id === null) || ($request->type === null), 500, '500 missing parameters');

        if (Gate::denies('audit_log_show')) {
            $user = auth()->user();
            $roleIds = $user->roles()->pluck('roles.id');

            $isCartographer = Cartographer::where('cartographiable_type', $request->type)
                ->where('cartographiable_id', $request->id)
                ->where(function ($q) use ($user, $roleIds) {
                    $q->where('user_id', $user->id)
                        ->orWhereIn('role_id', $roleIds);
                })
                ->exists();

            abort_if(! $isCartographer, Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        // Get the list
        $auditLogs =
            DB::table('audit_logs')
                ->select(
                    'audit_logs.id',
                    'description',
                    'subject_type',
                    'subject_id',
                    'users.name',
                    'user_id',
                    'host',
                    'properties',
                    'audit_logs.created_at'
                )
                ->join('users', 'users.id', '=', 'user_id')
                ->where('subject_id', $request->id)
                ->where('subject_type', $request->type)
                ->orderBy('audit_logs.id')
                ->get();

        abort_if($auditLogs->isEmpty(), 404, 'Not found');

        // JSON decode all properties and collect the union of their keys
        $keys = [];
        foreach ($auditLogs as $auditLog) {
            $auditLog->properties = self::decodeProperties($auditLog->properties);
            foreach (array_keys($auditLog->properties) as $key) {
                $keys[$key] = true;
            }
        }
        $keys = array_keys($keys);

        return view('admin.auditLogs.history', compact('auditLogs', 'keys'));
    }

    /**
     * Decode the raw `properties` column into an array.
     * Handles NULL (oversized models are stored without properties),
     * legacy double-encoded JSON strings and invalid JSON.
     */
    private static function decodeProperties(?string $raw): array
    {
        if ($raw === null) {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }
}
