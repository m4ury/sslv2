<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Persona;

class PersonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $personas = [
                ['rut' =>'11233817-9','apellido_paterno' =>'Aguilera','apellido_materno' =>'Aguilera','nombres' =>'Marta Alicia','fecha_nacimiento' => '1967-07-30', 'telefono' =>'923883863', 'direccion' => 'Población Carlos Jimenez 0296 '],
                ['rut' =>'10703654-7','apellido_paterno' =>'Ahumada','apellido_materno' =>'Acevedo','nombres' =>'Hugo del Carmen','fecha_nacimiento' => '1968-02-28', 'telefono' =>'', 'direccion' => ''],
                ['rut' =>'9461262-4','apellido_paterno' =>'Ahumada','apellido_materno' =>'Díaz','nombres' =>'Elfia Isabel del Carmen','fecha_nacimiento' => '1961-11-04', 'telefono' =>'940573282', 'direccion' => 'Villa Alborada, Pobl.Santa Gemita N° 165, '],
                ['rut' =>'6470152-5','apellido_paterno' =>'Ahumada','apellido_materno' =>'Varas','nombres' =>'Manuel Jesus','fecha_nacimiento' => '1950-11-12', 'telefono' =>'988956569', 'direccion' => 'Av. Cementerio S/Nº'],
                ['rut' =>'0','apellido_paterno' =>'Araya','apellido_materno' =>'Fuenzalida','nombres' =>'Tamara Valentina','fecha_nacimiento' => '1987-10-01', 'telefono' =>'', 'direccion' => ''],
                ['rut' =>'0','apellido_paterno' =>'Barnao','apellido_materno' =>'Aguilera','nombres' =>'Mayoret Edith','fecha_nacimiento' => '1987-10-01', 'telefono' =>'', 'direccion' => ''],
                ['rut' =>'23318912-k','apellido_paterno' =>'Caceres','apellido_materno' =>'Araya','nombres' =>'Alejandra Ignasia','fecha_nacimiento' => '2010-05-07', 'telefono' =>'981987150', 'direccion' => 'Av.11 Septiempre N° 449. '],
                ['rut' =>'9558745-3','apellido_paterno' =>'Castro','apellido_materno' =>'Valenzuela','nombres' =>'Maria ','fecha_nacimiento' => '1949-11-12', 'telefono' =>'hija 96639135', 'direccion' => 'Mira Rios S/N '],
                ['rut' =>'18595104-9','apellido_paterno' =>'Cerda','apellido_materno' =>'Pavez','nombres' =>'Luis Alberto','fecha_nacimiento' => '1994-07-10', 'telefono' =>'965141650', 'direccion' => 'Villa Nueva Esperanza Pje. Los Andes N°720 '],
                ['rut' =>'12206528-6','apellido_paterno' =>'Diaz','apellido_materno' =>'diaz','nombres' =>'Hernan Enrique','fecha_nacimiento' => '1972-02-25', 'telefono' =>'991224653', 'direccion' => 'Arturo Prat #737, '],
                ['rut' =>'20757379-5','apellido_paterno' =>'Díaz','apellido_materno' =>'Muñoz','nombres' =>'Francisco Antonio','fecha_nacimiento' => '2001-10-19', 'telefono' =>'991224653', 'direccion' => 'Arturo Prat #737, '],
                ['rut' =>'10827163-9','apellido_paterno' =>'Guerrero','apellido_materno' =>'Arce','nombres' =>'Quiterio del Carmen','fecha_nacimiento' => '1966-06-24', 'telefono' =>'', 'direccion' => 'Quilico N° 32'],
                ['rut' =>'16425167-5','apellido_paterno' =>'Gutierrez','apellido_materno' =>'Sáez','nombres' =>'Marcela Paz','fecha_nacimiento' => '1987-01-29', 'telefono' =>'935145775', 'direccion' => 'Diego portales interior S/Nº'],
                ['rut' =>'8379903-k','apellido_paterno' =>'Herandez','apellido_materno' =>'Bravo','nombres' =>'María Olga','fecha_nacimiento' => '1952-02-10', 'telefono' =>'930041521', 'direccion' => 'Parronal s/n Frente Ex Estación'],
                ['rut' =>'11576090-4','apellido_paterno' =>'Inzunza','apellido_materno' =>'Diaz','nombres' =>'Ramon Gerardo','fecha_nacimiento' => '1970-09-02', 'telefono' =>'979557119', 'direccion' => ''],
                ['rut' =>'5454945-8','apellido_paterno' =>'Lagos','apellido_materno' =>'Lagos','nombres' =>'Nubia del Carmen','fecha_nacimiento' => '1946-04-23', 'telefono' =>'', 'direccion' => 'Lautaro N°499 '],
                ['rut' =>'12390636-5','apellido_paterno' =>'Martínez','apellido_materno' =>'Olmeño','nombres' =>'Ilcia Amelia','fecha_nacimiento' => '1973-02-10', 'telefono' =>'938903996', 'direccion' => 'Av. Cementerio s/n '],
                ['rut' =>'10539087-4','apellido_paterno' =>'Mora','apellido_materno' =>'Espina','nombres' =>'Maria Cecilia','fecha_nacimiento' => '1971-02-28', 'telefono' =>'999123883', 'direccion' => 'Los coipos S/Nº , '],
                ['rut' =>'7000188-8','apellido_paterno' =>'Moraga','apellido_materno' =>'Valenzuela','nombres' =>'Luis Francisco','fecha_nacimiento' => '1952-10-03', 'telefono' =>'971075838', 'direccion' => 'Villa el Mirador Nº 1540 Villas Norte . '],
                ['rut' =>'19610955-2','apellido_paterno' =>'Morales','apellido_materno' =>'Reyes','nombres' =>'Juan Ariel','fecha_nacimiento' => '1997-08-01', 'telefono' =>'935791319', 'direccion' => 'Samuel Baeza N° 75. Camino al Puente '],
                ['rut' =>'19527397-9','apellido_paterno' =>'Morales','apellido_materno' =>'Salgado','nombres' =>'Lorena Rosa','fecha_nacimiento' => '1997-04-22', 'telefono' =>'944376316', 'direccion' => 'Pasaje San Juan, Paradero 39, el Porvenir '],
                ['rut' =>'12206646-0','apellido_paterno' =>'Muñoz','apellido_materno' =>'Faúndez','nombres' =>'Sergio Andrés','fecha_nacimiento' => '1973-11-10', 'telefono' =>'56755593', 'direccion' => 'Camino al costado de colegio, Peralillo'],
                ['rut' =>'9945027-4','apellido_paterno' =>'Muñoz','apellido_materno' =>'López','nombres' =>'Norma del Carmen','fecha_nacimiento' => '1965-09-09', 'telefono' =>'964830933', 'direccion' => 'Villa Alborada, pasaje San José, casa Nª400. Hualañè'],
                ['rut' =>'17882951-3','apellido_paterno' =>'Naranjo','apellido_materno' =>'Muñoz','nombres' =>'Ariel Alejandro','fecha_nacimiento' => '2001-10-19', 'telefono' =>'', 'direccion' => ''],
                ['rut' =>'8641692-1','apellido_paterno' =>'Nuñez','apellido_materno' =>'Rojas','nombres' =>'Cecilia De Las Mercedes','fecha_nacimiento' => '1958-06-26', 'telefono' =>'', 'direccion' => 'Pje. Las Asusenas N°4. El Porvenir'],
                ['rut' =>'18683253-5','apellido_paterno' =>'Nuñez','apellido_materno' =>'Vidal','nombres' =>'Cristopher Alejandro','fecha_nacimiento' => '1994-09-25', 'telefono' =>'966218158', 'direccion' => 'Calle los Andes N° 25'],
                ['rut' =>'18525960-9','apellido_paterno' =>'Pino','apellido_materno' =>'Rojas','nombres' =>'Héctor Alexis','fecha_nacimiento' => '1993-09-25', 'telefono' =>'982261029', 'direccion' => 'Mira Río S/N'],
                ['rut' =>'16387479-2','apellido_paterno' =>'Pizarro','apellido_materno' =>'Guerra','nombres' =>'Jonathan Andres','fecha_nacimiento' => '1987-01-20', 'telefono' =>'985719111', 'direccion' => 'Psje San Jose #340 Villa Alborada'],
                ['rut' =>'8441584-7','apellido_paterno' =>'Reyes','apellido_materno' =>'Fantobal','nombres' =>'María Mercedes','fecha_nacimiento' => '1959-06-14', 'telefono' =>'975560506', 'direccion' => 'Samuel Baeza N° 75. Camino al Puente. '],
                ['rut' =>'7828277-0','apellido_paterno' =>'Riveros','apellido_materno' =>'González','nombres' =>'Carlos Honorio','fecha_nacimiento' => '1956-01-14', 'telefono' =>'965718375', 'direccion' => 'Hijuela, sta maria s/n km 69, '],
                ['rut' =>'10474970-4','apellido_paterno' =>'Rodiguez','apellido_materno' =>'Avendaño','nombres' =>'Maria Luisa','fecha_nacimiento' => '1975-07-12', 'telefono' =>'', 'direccion' => 'Calle los Andes -n° 35'],
                ['rut' =>'8472535-8','apellido_paterno' =>'Rodríguez','apellido_materno' =>'Espina','nombres' =>'María Inés','fecha_nacimiento' => '1948-05-10', 'telefono' =>'956498550', 'direccion' => 'Rinconada De Nuñez'],
                ['rut' =>'7458285-0','apellido_paterno' =>'Rojas','apellido_materno' =>'Miranda','nombres' =>'Rosa Clara','fecha_nacimiento' => '1955-04-27', 'telefono' =>'982229623', 'direccion' => 'Pasaje González N° 9, Carlos Jimenez '],
                ['rut' =>'13362295-0','apellido_paterno' =>'Tapia','apellido_materno' =>'Zapata','nombres' =>'Darwin Sadino','fecha_nacimiento' => '1978-02-22', 'telefono' =>'931489509', 'direccion' => 'Diego portales interior S/Nº'],
                ['rut' =>'12200825-8','apellido_paterno' =>'Urrutia','apellido_materno' =>'Obando','nombres' =>'Claudio Marcelo','fecha_nacimiento' => '1972-07-04', 'telefono' =>'975826367', 'direccion' => 'Claudio Arrau, esquina los Andes s/n'],
                ['rut' =>'7672201-3','apellido_paterno' =>'Valdivia','apellido_materno' =>'Caceres','nombres' =>'Emelino del Carmen','fecha_nacimiento' => '1955-08-08', 'telefono' =>'982662048', 'direccion' => 'El Molino s/n, ruta J60'],
                ['rut' =>'13784257-2','apellido_paterno' =>'Véliz','apellido_materno' =>'Maturana','nombres' =>'José Agusto','fecha_nacimiento' => '1980-09-11', 'telefono' =>'', 'direccion' => 'Edecan Araya s/n'],
                ['rut' =>'11787163-0','apellido_paterno' =>'Véliz','apellido_materno' =>'Maturana','nombres' =>'Marisol de Dios','fecha_nacimiento' => '1971-01-10', 'telefono' =>'97190932', 'direccion' => 'Edecan Araya s/n'],
                ['rut' =>'11787301-3','apellido_paterno' =>'Vergara','apellido_materno' =>'Castro','nombres' =>'Juan Felipe','fecha_nacimiento' => '1971-09-11', 'telefono' =>'', 'direccion' => 'Mira rios s/n'],
                ['rut' =>'7865888-6','apellido_paterno' =>'Vergara','apellido_materno' =>'Díaz','nombres' =>'Virginia Ester','fecha_nacimiento' => '1961-05-15', 'telefono' =>'982228705', 'direccion' => 'Constantue s/n'],
                ['rut' =>'19527362-6','apellido_paterno' =>'Vergara','apellido_materno' =>'Maldonado','nombres' =>'John Jorge Sebastian','fecha_nacimiento' => '1996-01-31', 'telefono' =>'947339796', 'direccion' => 'Av. Cementerio #75'],
                ['rut' =>'0','apellido_paterno' =>'Ahumada','apellido_materno' =>'','nombres' =>'Manuel ','fecha_nacimiento' => '1987-10-01', 'telefono' =>'', 'direccion' => ''],
                ['rut' =>'0','apellido_paterno' =>'Valenzuela ','apellido_materno' =>'','nombres' =>'Francisco ','fecha_nacimiento' => '1987-10-01', 'telefono' =>'', 'direccion' => ''],
                ['rut' =>'6055055-7','apellido_paterno' =>'Baeza ','apellido_materno' =>'Díaz','nombres' =>'Brígida Ester','fecha_nacimiento' => '1950-04-17', 'telefono' =>'998027521', 'direccion' => 'Rinconada de Nuñez Parcela N°6'],
                ['rut' =>'93260369-1','apellido_paterno' =>'Anonimo ','apellido_materno' =>'Ssl','nombres' =>'Colaborador','fecha_nacimiento' => '1987-04-17', 'telefono' =>'000000000', 'direccion' => 'Lautaro'],
            ];
        foreach ($personas as $persona)
        {
            Persona::updateOrCreate(['rut' => $persona['rut']],
                ['nombres' => $persona['nombres'], 'apellido_paterno' => $persona['apellido_paterno'],
                'apellido_materno' => $persona['apellido_materno'],
                'fecha_nacimiento' => $persona['fecha_nacimiento'], 'direccion' => $persona['direccion'],
                'telefono' => $persona['telefono'], 'comuna' => 'Hualane', 'sexo' => 'Femenino'
                ]
            );
        }
    }
}
