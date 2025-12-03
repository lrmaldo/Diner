<?php

namespace Tests\Feature\Livewire;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientsIndexStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_shows_success_message_after_deleting_cliente(): void
    {
        // Crear usuario con permiso simulado (simplificado: asumir policy/gate ya permite o asignar rol si existiera)
        $user = User::factory()->create();
        $this->be($user);

        $cliente = Cliente::factory()->create();

        Livewire::test(\App\Livewire\Clients\Index::class)
            ->call('confirmDelete', $cliente->id)
            ->call('deleteConfirmed')
            ->assertSet('statusType', 'success')
            ->assertSet('statusMessage', 'Cliente eliminado correctamente')
            ->assertSee('Cliente eliminado correctamente');
    }
}
