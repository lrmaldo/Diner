<?php

namespace Tests\Feature;

use App\Livewire\Caja\ArqueoCaja;
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
            ->test(ArqueoCaja::class)
            ->call('abrirCambios')
            ->set('billetesCambioEntrada.500', 2)
            ->set('billetesCambioSalida.200', 5)
            ->call('guardarCambios')
            ->assertSet('showCambiosModal', false)
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
}
