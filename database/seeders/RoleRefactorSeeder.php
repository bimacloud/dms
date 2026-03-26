<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;

class RoleRefactorSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Positions
        $positions = ['Manager', 'NOC', 'CS', 'Teknisi'];
        $positionMap = [];
        foreach ($positions as $posName) {
            $pos = Position::firstOrCreate(['name' => $posName]);
            $positionMap[$posName] = $pos->id;
        }

        // 2. Refactor Roles
        $managerRole = Role::where('name', 'Manager')->first();
        if ($managerRole) {
            $managerRole->update(['name' => 'admin']);
        } else {
            $managerRole = Role::firstOrCreate(['name' => 'admin']);
        }

        $nocRole = Role::where('name', 'NOC')->first();
        if ($nocRole) {
            $nocRole->update(['name' => 'member']);
        } else {
            $nocRole = Role::firstOrCreate(['name' => 'member']);
        }

        // 3. Map old roles CS and Teknisi to member (which is $nocRole)
        $csRole = Role::where('name', 'CS')->first();
        $tekRole = Role::where('name', 'Teknisi')->first();

        // 4. Migrate users currently assuming these old role paths
        foreach (User::all() as $user) {
            // Assign positions and remap roles
            if ($user->role_id === $managerRole->id) {
                // Previously Manager -> Now Admin + Manager Position
                $user->update(['position_id' => $positionMap['Manager']]);
            } elseif ($user->role_id === $nocRole->id) {
                // Previously NOC -> Now Member + NOC Position
                $user->update(['position_id' => $positionMap['NOC']]);
            } elseif ($csRole && $user->role_id === $csRole->id) {
                // Previously CS -> Now Member + CS Position
                $user->update([
                    'role_id' => $nocRole->id,
                    'position_id' => $positionMap['CS']
                ]);
            } elseif ($tekRole && $user->role_id === $tekRole->id) {
                // Previously Teknisi -> Now Member + Teknisi Position
                $user->update([
                    'role_id' => $nocRole->id,
                    'position_id' => $positionMap['Teknisi']
                ]);
            }
        }

        // 5. Safely delete obsolete roles
        if ($csRole) {
            DB::table('role_menu')->where('role_id', $csRole->id)->delete();
            $csRole->delete();
        }
        if ($tekRole) {
            DB::table('role_menu')->where('role_id', $tekRole->id)->delete();
            $tekRole->delete(); 
        }
    }
}
