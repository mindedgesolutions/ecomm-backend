<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 50; $i++) {
            $customer = User::create([
                'name' => $faker->name,
                'email' => $faker->email,
                'email_verified_at' => now(),
                'mobile' => $faker->phoneNumber,
                'password' => bcrypt('password'),
            ])->assignRole('customer');

            $arr = explode(' ', $customer->name);
            $firstName = $arr[0];
            $lastNameArr = array_splice($arr, 1);
            $lastName = implode(' ', $lastNameArr);

            $customer->userDetail()->create([
                'user_id' => $customer->id,
                'slug' => Str::slug($customer->name),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'avatar' => $faker->imageUrl(200, 200),
            ]);

            $customer->customerAddress()->create([
                'user_id' => $customer->id,
                'name' => $customer->name,
                'mobile' => $customer->mobile,
                'address_line_1' => $faker->streetAddress,
                'city' => $faker->city,
                'state' => $faker->state,
                'pincode' => $faker->postcode,
                'landmark' => $faker->secondaryAddress,
                'type' => 'home',
                'is_default' => true,
            ]);
        }
    }
}
