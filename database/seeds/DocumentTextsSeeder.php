<?php

use Illuminate\Database\Seeder;

class DocumentTextsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->text('document.title', 'LASTENHEFT');
        $this->text('document.subtitle', 'zur Auswahl eines ERP-Systems');
        $this->text('document.copyright', 'Erpplanner.com - powered by Evolvio GmbH & abas Software AG');
        $this->text('document.logoPlaceholder', '[Ihr Logo]');
        $this->text('document.tocTitle', 'Inhaltsverzeichnis');
    }

    protected function text($key, $value)
    {
        \App\Models\Text::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
