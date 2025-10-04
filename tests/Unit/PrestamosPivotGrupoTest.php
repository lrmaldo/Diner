<?php

namespace Tests\Unit;

use App\Livewire\Prestamos\Create as PrestamoCreate;
use App\Models\Cliente;
use App\Models\Prestamo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PrestamosPivotGrupoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function seleccion_de_cliente_grupal_persiste_en_pivot()
    {
        // crear préstamo grupal inicial
        $prestamo = Prestamo::create([
            'producto' => 'grupal',
            'plazo' => '4meses',
            'periodicidad' => 'semanal',
            'periodo_pago' => null,
            'fecha_entrega' => now()->toDateString(),
            'fecha_primer_pago' => null,
            'dia_pago' => 'Lunes',
            'monto_total' => 0,
            'tasa_interes' => 4.5,
            'estado' => 'en_curso',
        ]);

        $cliente = Cliente::create([
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Lopez',
            'nombres' => 'Juan',
            'curp' => 'CURPJUAN123456789',
            'calle_numero' => 'Calle 1',
        ]);

        Livewire::test(PrestamoCreate::class)
            ->set('prestamo_id', $prestamo->id)
            ->set('producto', 'grupal')
            ->set('grupo_id', 1) // simular grupo ya elegido
            ->call('selectCliente', $cliente->id);

        $this->assertDatabaseHas('cliente_prestamo', [
            'prestamo_id' => $prestamo->id,
            'cliente_id' => $cliente->id,
        ]);
    }
}
