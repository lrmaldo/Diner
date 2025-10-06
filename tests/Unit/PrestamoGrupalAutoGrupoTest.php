<?php

namespace Tests\Unit;

use App\Livewire\Prestamos\Create as PrestamoCreate;
use App\Models\Prestamo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PrestamoGrupalAutoGrupoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function crea_grupo_automatico_y_no_muestra_botones_buscar_o_nuevo()
    {
        Livewire::test(PrestamoCreate::class)
            ->set('producto', 'grupal')
            ->set('plazo', '4meses')
            ->set('periodicidad', 'semanal')
            ->set('fecha_entrega', now()->toDateString())
            ->set('dia_pago', 'lunes')
            ->call('crearPrestamo')
            ->assertSet('step', 2)
            ->assertSee('Agregar cliente existente')
            ->assertDontSee('Buscar grupo')
            ->assertDontSee('Nuevo grupo');

        $this->assertDatabaseCount('grupos', 1);
        $this->assertDatabaseHas('grupos', ['nombre' => 'representante grupal']);
        $prestamo = Prestamo::first();
        $this->assertNotNull($prestamo->grupo_id);
    }
}
