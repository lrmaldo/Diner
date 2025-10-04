<?php

namespace Tests\Unit;

use App\Livewire\Prestamos\Create as PrestamoCreate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PrestamosEnviarAComiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_boton_enviar_a_comite_en_individual(): void
    {
        Livewire::test(PrestamoCreate::class)
            ->set('producto', 'individual')
            ->set('step', 2)
            ->assertSee('Enviar a comité');
    }

    public function test_muestra_boton_enviar_a_comite_en_grupal(): void
    {
        Livewire::test(PrestamoCreate::class)
            ->set('producto', 'grupal')
            ->set('step', 2)
            // simular que ya hay un miembro en memoria para que aparezcan las acciones
            ->set('clientesAgregados', [
                ['cliente_id' => 999, 'nombre' => 'Cliente Prueba', 'monto_solicitado' => 1000],
            ])
            ->assertSee('Enviar a comité')
            ->assertDontSee('Finalizar vinculación');
    }
}
