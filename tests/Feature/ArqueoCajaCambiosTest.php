<?php

namespace Tests\Feature;

use App\Livewire\Caja\Cambios;
use App\Models\Capitalizacion;
use App\Models\Egreso;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArqueoCajaCambiosTest extends TestCase
{
    use RefreshDatabase;

    public function test_aplica_cambio_con_ingreso_y_salida_en_un_solo_movimiento(): void
    {
        Role::create([
            'name' => 'Administrador',
            'guard_name' => 'web',
            'slug' => 'administrador',
        ]);

        $user = User::factory()->create();
        $user->assignRole('Administrador');

        Livewire::actingAs($user)
            ->test(Cambios::class)
            ->set('billetesCambioEntrada.500', 2)
            ->call('aceptarIngresoCambio')
            ->assertSet('pasoCambio', 'sale')
            ->set('billetesCambioSalida.200', 5)
            ->call('guardarCambios')
            ->assertSet('pasoCambio', 'ingresa');

        $this->assertDatabaseCount('capitalizaciones', 1);
        $this->assertDatabaseCount('egresos', 1);

        $capitalizacion = Capitalizacion::first();
        $this->assertSame('1000.00', number_format((float) $capitalizacion->monto, 2, '.', ''));

        $egreso = Egreso::first();
        $this->assertSame('1000.00', number_format((float) $egreso->monto, 2, '.', ''));
        $this->assertSame(5, data_get($egreso->denominaciones, 'billetes.200'));
        $this->assertSame(2, data_get($capitalizacion->desglose_billetes, 'billetes.500'));
    }

    public function test_no_permite_cambio_si_el_monto_que_sale_no_es_exacto(): void
    {
        Role::create([
            'name' => 'Administrador',
            'guard_name' => 'web',
            'slug' => 'administrador',
        ]);

        $user = User::factory()->create();
        $user->assignRole('Administrador');

        // Ingresa 1000, intenta sacar 900 (de menos): no debe aplicar.
        Livewire::actingAs($user)
            ->test(Cambios::class)
            ->set('billetesCambioEntrada.1000', 1)
            ->call('aceptarIngresoCambio')
            ->set('billetesCambioSalida.500', 1)
            ->set('billetesCambioSalida.200', 2)
            ->call('guardarCambios')
            ->assertSet('pasoCambio', 'sale'); // sigue en 'sale', no se completó

        $this->assertDatabaseCount('capitalizaciones', 0);
        $this->assertDatabaseCount('egresos', 0);

        // Ahora intenta sacar 1100 (de más): tampoco debe aplicar.
        Livewire::actingAs($user)
            ->test(Cambios::class)
            ->set('billetesCambioEntrada.1000', 1)
            ->call('aceptarIngresoCambio')
            ->set('billetesCambioSalida.500', 2)
            ->set('billetesCambioSalida.100', 1)
            ->call('guardarCambios')
            ->assertSet('pasoCambio', 'sale');

        $this->assertDatabaseCount('capitalizaciones', 0);
        $this->assertDatabaseCount('egresos', 0);
    }
}
