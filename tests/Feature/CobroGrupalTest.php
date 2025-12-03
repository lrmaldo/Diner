<?php

namespace Tests\Feature;

use App\Livewire\Pagos\CobroGrupal;
use App\Models\Cliente;
use App\Models\Grupo;
use App\Models\Pago;
use App\Models\Permission;
use App\Models\Prestamo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CobroGrupalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear permisos y roles necesarios
        Permission::create(['name' => 'ver prestamos']);
        Permission::create(['name' => 'registrar pagos']);

        $adminRole = Role::create(['name' => 'Administrador']);
        $adminRole->givePermissionTo(['ver prestamos', 'registrar pagos']);
    }

    public function test_usuarios_no_autenticados_no_pueden_acceder(): void
    {
        $prestamo = Prestamo::factory()->create(['producto' => 'grupal']);

        $this->get(route('pagos.cobro-grupal', ['prestamoId' => $prestamo->id]))
            ->assertRedirect('/login');
    }

    public function test_usuarios_autenticados_pueden_acceder_a_cobro_grupal(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Administrador');

        $grupo = Grupo::factory()->create();
        $prestamo = Prestamo::factory()->create([
            'producto' => 'grupal',
            'grupo_id' => $grupo->id,
            'estado' => 'autorizado',
        ]);

        $this->actingAs($user)
            ->get(route('pagos.cobro-grupal', ['prestamoId' => $prestamo->id]))
            ->assertOk()
            ->assertSeeLivewire(CobroGrupal::class);
    }

    public function test_componente_carga_prestamo_correctamente(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Administrador');

        $grupo = Grupo::factory()->create(['nombre' => 'Grupo Test']);
        $representante = Cliente::factory()->create();

        $prestamo = Prestamo::factory()->create([
            'producto' => 'grupal',
            'grupo_id' => $grupo->id,
            'representante_id' => $representante->id,
            'estado' => 'autorizado',
            'plazo' => 12,
            'tasa_interes' => 15,
        ]);

        Livewire::actingAs($user)
            ->test(CobroGrupal::class, ['prestamoId' => $prestamo->id])
            ->assertSet('prestamo.id', $prestamo->id)
            ->assertSee($grupo->nombre);
    }

    public function test_puede_seleccionar_clientes_para_cobrar(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Administrador');

        $grupo = Grupo::factory()->create();
        $prestamo = Prestamo::factory()->create([
            'producto' => 'grupal',
            'grupo_id' => $grupo->id,
            'estado' => 'autorizado',
        ]);

        $cliente1 = Cliente::factory()->create();
        $cliente2 = Cliente::factory()->create();

        $prestamo->clientes()->attach($cliente1->id, [
            'monto_solicitado' => 5000,
            'monto_autorizado' => 5000,
        ]);
        $prestamo->clientes()->attach($cliente2->id, [
            'monto_solicitado' => 3000,
            'monto_autorizado' => 3000,
        ]);

        Livewire::actingAs($user)
            ->test(CobroGrupal::class, ['prestamoId' => $prestamo->id])
            ->set('clientesSeleccionados.'.$cliente1->id, true)
            ->assertSet('clientesSeleccionados.'.$cliente1->id, true)
            ->assertSet('clientesSeleccionados.'.$cliente2->id, false);
    }

    public function test_calcula_total_seleccionado_correctamente(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Administrador');

        $grupo = Grupo::factory()->create();
        $prestamo = Prestamo::factory()->create([
            'producto' => 'grupal',
            'grupo_id' => $grupo->id,
            'estado' => 'autorizado',
            'plazo' => 10,
            'tasa_interes' => 10,
        ]);

        $cliente = Cliente::factory()->create();
        $prestamo->clientes()->attach($cliente->id, [
            'monto_autorizado' => 1000,
        ]);

        $component = Livewire::actingAs($user)
            ->test(CobroGrupal::class, ['prestamoId' => $prestamo->id])
            ->set('clientesSeleccionados.'.$cliente->id, true)
            ->set('montosPorCliente.'.$cliente->id, 500)
            ->set('moratoriosPorCliente.'.$cliente->id, 50);

        // El total debe ser 550 (500 + 50)
        $this->assertEquals(550, $component->get('totalSeleccionado'));
    }

    public function test_calcula_total_efectivo_desde_desglose(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Administrador');

        $prestamo = Prestamo::factory()->create(['producto' => 'grupal', 'estado' => 'autorizado']);

        $component = Livewire::actingAs($user)
            ->test(CobroGrupal::class, ['prestamoId' => $prestamo->id])
            ->set('desgloseBilletes.1000', 5)  // 5000
            ->set('desgloseBilletes.500', 2)   // 1000
            ->set('desgloseMonedas.10', 10);   // 100

        // Total: 5000 + 1000 + 100 = 6100
        $this->assertEquals(6100, $component->get('totalEfectivo'));
    }

    public function test_calcula_diferencia_correctamente(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Administrador');

        $grupo = Grupo::factory()->create();
        $prestamo = Prestamo::factory()->create([
            'producto' => 'grupal',
            'grupo_id' => $grupo->id,
            'estado' => 'autorizado',
        ]);

        $cliente = Cliente::factory()->create();
        $prestamo->clientes()->attach($cliente->id, ['monto_autorizado' => 1000]);

        $component = Livewire::actingAs($user)
            ->test(CobroGrupal::class, ['prestamoId' => $prestamo->id])
            ->set('clientesSeleccionados.'.$cliente->id, true)
            ->set('montosPorCliente.'.$cliente->id, 500)
            ->set('desgloseBilletes.1000', 1);  // 1000

        // Diferencia: 1000 (efectivo) - 500 (a cobrar) = 500
        $this->assertEquals(500, $component->get('diferencia'));
    }

    public function test_no_puede_registrar_pagos_sin_seleccionar_clientes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Administrador');

        $prestamo = Prestamo::factory()->create(['producto' => 'grupal', 'estado' => 'autorizado']);

        Livewire::actingAs($user)
            ->test(CobroGrupal::class, ['prestamoId' => $prestamo->id])
            ->call('registrarPagos')
            ->assertDispatched('alert', function ($event) {
                return $event['type'] === 'error' &&
                       str_contains($event['message'], 'seleccionar al menos un cliente');
            });
    }

    public function test_no_puede_registrar_pagos_sin_efectivo(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Administrador');

        $grupo = Grupo::factory()->create();
        $prestamo = Prestamo::factory()->create([
            'producto' => 'grupal',
            'grupo_id' => $grupo->id,
            'estado' => 'autorizado',
        ]);

        $cliente = Cliente::factory()->create();
        $prestamo->clientes()->attach($cliente->id, ['monto_autorizado' => 1000]);

        Livewire::actingAs($user)
            ->test(CobroGrupal::class, ['prestamoId' => $prestamo->id])
            ->set('clientesSeleccionados.'.$cliente->id, true)
            ->set('montosPorCliente.'.$cliente->id, 500)
            ->call('registrarPagos')
            ->assertDispatched('alert', function ($event) {
                return $event['type'] === 'error' &&
                       str_contains($event['message'], 'ingresar el efectivo');
            });
    }

    public function test_no_puede_registrar_pagos_con_efectivo_insuficiente(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Administrador');

        $grupo = Grupo::factory()->create();
        $prestamo = Prestamo::factory()->create([
            'producto' => 'grupal',
            'grupo_id' => $grupo->id,
            'estado' => 'autorizado',
        ]);

        $cliente = Cliente::factory()->create();
        $prestamo->clientes()->attach($cliente->id, ['monto_autorizado' => 1000]);

        Livewire::actingAs($user)
            ->test(CobroGrupal::class, ['prestamoId' => $prestamo->id])
            ->set('clientesSeleccionados.'.$cliente->id, true)
            ->set('montosPorCliente.'.$cliente->id, 1000)
            ->set('desgloseBilletes.500', 1)  // Solo 500
            ->call('registrarPagos')
            ->assertDispatched('alert', function ($event) {
                return $event['type'] === 'error' &&
                       str_contains($event['message'], 'insuficiente');
            });
    }

    public function test_registra_pagos_exitosamente(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Administrador');

        $grupo = Grupo::factory()->create();
        $prestamo = Prestamo::factory()->create([
            'producto' => 'grupal',
            'grupo_id' => $grupo->id,
            'estado' => 'autorizado',
        ]);

        $cliente1 = Cliente::factory()->create();
        $cliente2 = Cliente::factory()->create();

        $prestamo->clientes()->attach($cliente1->id, ['monto_autorizado' => 1000]);
        $prestamo->clientes()->attach($cliente2->id, ['monto_autorizado' => 1000]);

        Livewire::actingAs($user)
            ->test(CobroGrupal::class, ['prestamoId' => $prestamo->id])
            ->set('clientesSeleccionados.'.$cliente1->id, true)
            ->set('clientesSeleccionados.'.$cliente2->id, true)
            ->set('montosPorCliente.'.$cliente1->id, 500)
            ->set('montosPorCliente.'.$cliente2->id, 300)
            ->set('desgloseBilletes.1000', 1)  // 1000
            ->call('registrarPagos');

        // Verificar que se crearon los pagos
        $this->assertDatabaseCount('pagos', 2);

        $pago1 = Pago::where('cliente_id', $cliente1->id)->first();
        $this->assertEquals(500, $pago1->monto);
        $this->assertEquals($prestamo->id, $pago1->prestamo_id);

        $pago2 = Pago::where('cliente_id', $cliente2->id)->first();
        $this->assertEquals(300, $pago2->monto);
    }

    public function test_seleccionar_todos_selecciona_todos_los_clientes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Administrador');

        $grupo = Grupo::factory()->create();
        $prestamo = Prestamo::factory()->create([
            'producto' => 'grupal',
            'grupo_id' => $grupo->id,
            'estado' => 'autorizado',
        ]);

        $cliente1 = Cliente::factory()->create();
        $cliente2 = Cliente::factory()->create();
        $cliente3 = Cliente::factory()->create();

        $prestamo->clientes()->attach($cliente1->id, ['monto_autorizado' => 1000]);
        $prestamo->clientes()->attach($cliente2->id, ['monto_autorizado' => 1000]);
        $prestamo->clientes()->attach($cliente3->id, ['monto_autorizado' => 1000]);

        $component = Livewire::actingAs($user)
            ->test(CobroGrupal::class, ['prestamoId' => $prestamo->id])
            ->set('seleccionarTodos', true);

        $this->assertTrue($component->get('clientesSeleccionados.'.$cliente1->id));
        $this->assertTrue($component->get('clientesSeleccionados.'.$cliente2->id));
        $this->assertTrue($component->get('clientesSeleccionados.'.$cliente3->id));
    }
}
