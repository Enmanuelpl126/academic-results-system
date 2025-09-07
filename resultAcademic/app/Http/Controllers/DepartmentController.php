<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class DepartmentController extends Controller
{
    /**
     * Store a newly created department in storage.
     * Datos requeridos: name. Opcionales: description, head_user_id.
     * La regla de negocio exige que el Jefe de Departamento sea miembro del mismo,
     * por lo que, si se envía head_user_id, se asigna ese usuario al nuevo departamento
     * y se guarda como jefe.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'head_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($q) {
                    $q->whereNull('department_id')->where('is_enabled', true);
                }),
            ],
        ]);

        return DB::transaction(function () use ($validated) {
            // Crear el departamento
            $department = new Department();
            $department->name = $validated['name'];
            $department->description = $validated['description'] ?? null;
            $department->save();

            // Si se especifica jefe, asignarlo como miembro y como jefe
            if (!empty($validated['head_user_id'])) {
                $head = User::findOrFail($validated['head_user_id']);
                // Asignar al nuevo departamento (membresía)
                $head->department_id = $department->id;
                $head->save();

                // Marcar como Jefe de Departamento
                $department->head_of_department_id = $head->id;
                $department->save();

                // Cambiar rol del usuario a Jefe de Departamento
                if (method_exists($head, 'syncRoles')) {
                    $head->syncRoles(['head_dp']);
                }
            }

            return redirect()->route('admin.index')
                ->with('success', 'Departamento creado correctamente.');
        });
    }

    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, string $id)
    {
        $department = Department::findOrFail($id);
        $hasCurrentHead = !empty($department->head_of_department_id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments', 'name')->ignore($department->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'head_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($q) use ($department, $hasCurrentHead) {
                    $q->where('is_enabled', true)
                      ->where(function ($w) use ($department, $hasCurrentHead) {
                          // Siempre permitir miembros del departamento actual
                          $w->where('department_id', $department->id);
                          // Si el departamento no tiene jefe, permitir también usuarios sin departamento
                          if (!$hasCurrentHead) {
                              $w->orWhereNull('department_id');
                          }
                      });
                }),
            ],
        ]);

        return DB::transaction(function () use ($department, $validated) {
            $department->name = $validated['name'];
            $department->description = $validated['description'] ?? null;

            // Guardar jefe anterior para actualizar roles si cambia
            $oldHeadId = $department->head_of_department_id;

            // Establecer/limpiar jefe si viene el campo
            if (array_key_exists('head_user_id', $validated)) {
                $department->head_of_department_id = $validated['head_user_id'] ?: null;
            }

            $department->save();

            // Si hay jefe, asegurar rol y membresía (si venía sin departamento)
            if (!empty($validated['head_user_id'])) {
                $head = User::find($validated['head_user_id']);
                if ($head) {
                    // Mover al departamento si no tenía
                    if (empty($head->department_id)) {
                        $head->department_id = $department->id;
                        $head->save();
                    }
                    if (method_exists($head, 'syncRoles')) {
                        $head->syncRoles(['head_dp']);
                    }
                }
            }

            // Si el jefe cambió o fue limpiado, el anterior pierde el rol de jefe -> profesor
            $newHeadId = $department->head_of_department_id;
            if ($oldHeadId && $oldHeadId !== $newHeadId) {
                $previousHead = User::find($oldHeadId);
                if ($previousHead && method_exists($previousHead, 'syncRoles')) {
                    $previousHead->syncRoles(['profesor']);
                }
            }

            return redirect()->route('admin.index')
                ->with('success', 'Departamento actualizado correctamente.');
        });
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(string $id)
    {
        $department = Department::findOrFail($id);

        return DB::transaction(function () use ($department) {
            // Si hay un jefe asignado, cambiar su rol a profesor
            if ($department->head_of_department_id) {
                $head = User::find($department->head_of_department_id);
                if ($head && method_exists($head, 'syncRoles')) {
                    $head->syncRoles(['profesor']);
                }
            }

            // Usuarios del departamento pasan a no tener departamento
            // La FK ya está con onDelete('set null'), pero actualizamos explícitamente por claridad
            User::where('department_id', $department->id)->update(['department_id' => null]);

            // Eliminar el departamento
            $department->delete();

            return redirect()->route('admin.index')
                ->with('success', 'Departamento eliminado correctamente.');
        });
    }
}
