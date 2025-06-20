<?php

namespace App\Services\Import;

use App\Models\CostCenter;
use App\Models\CostCenterType;
use App\Models\User;
use App\Models\Workgroup;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * CostCenterImporter is responsible for importing cost center data from a CSV file.
 * It validates the file format, checks each row for correctness, and saves valid rows to the database.
 */
class CostCenterImporter extends BaseImporter
{
    /**
     * Set the rules for validating each row of the CSV file.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            '0' => 'required|regex:/^\d{4}-\d{2} \d{3}$/|unique:wf_cost_center,cost_center_code',
            '1' => 'required|string|max:255',
            '2' => 'required',
            '3' => 'required',
            '4' => 'required',
            '5' => 'nullable|date_format:Y.m.d|after_or_equal:today',
        ];
    }

    /**
     * Save a row of data to the database.
     *
     * @param array $row
     * @param int $index
     */
    protected function saveRow(array $row, int $index): void
    {
        // Skip first row as it is the header
        if ($index === 0) {
            return;
        }

        // Check if the row is entirely empty (e.g., ';;;;;') as it should not be imported
        if (count(array_filter($row, function($value) { return $value !== ''; })) === 0) {
            return;
        }

        $costCenterCode = $row[0];
        $costCenterName = $row[1];
        $costCenterType = CostCenterType::where('name', $row[2])->first();
        $leadUser = User::where('name', $row[3])->first();
        $projectCoordinator = User::where('name', $row[4])->first();
        $dueDate = !empty($row[5]) ? Carbon::createFromFormat('Y.m.d', $row[5]) : null;

        CostCenter::create([
            'cost_center_code' => $costCenterCode,
            'name' => $costCenterName,
            'type_id' => $costCenterType->id,
            'lead_user_id' => $leadUser->id,
            'project_coordinator_user_id' => $projectCoordinator->id,
            'due_date' => $dueDate,
            'minimal_order_limit' => 0,
            'valid_employee_recruitment' => true,
            'valid_procurement' => true,
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);
    }

    /**
     * Validate each row of the CSV file.
     *
     * @param array $row
     * @param int $index
     * @return bool
     */
    protected function validateRow(array $row, int $index): bool
    {
        // Check if the row is entirely empty (e.g., ';;;;;')
        if (count(array_filter($row, function($value) { return $value !== ''; })) === 0) {
            return true;
        }

        // Validate the first row as the header
        if ($index === 0) {
            $header = ["Költséghely", "Megnevezés", "Típus", "Témavezetõ", "Projektkoordinátor", "Lejárat"];
            if ($row !== $header) {
                $this->errors[$index][] = "A feltölteni kívánt fájl fejlécének pontosan 'Költséghely;Megnevezés;Típus;Témavezetõ;Projektkoordinátor;Lejárat' értékeket kell tartalmaznia!";
                return false;
            }
            // Skip further validation for the header row
            return true;
        }

        $validator = Validator::make($row, $this->rules(), $this->validationMessages());

        if ($validator->fails()) {
            $this->errors[$index] = $validator->errors()->all();
            return false;
        }

        // 1. oszlop
        // Custom validation for group number in cost_center_code
        $groupNumber = substr($row[0], -3);
        if (!Workgroup::where('workgroup_number', $groupNumber)->exists()) {
            $this->errors[$index][] = "A(z) {$row[0]} költséghelyszámban szereplő csoport nem létezik!";
            return false;
        }

        // 3. oszlop
        // Custom validation for cost center type (Column 3) using CostCenterType model
        $type = $row[2];
        if (!CostCenterType::where('name', $type)->where('deleted', 0)->exists()) {
            $this->errors[$index][] = "A(z) {$row[0]} költséghely típusa nem szerepel az érvényes költséghely típusok között!";
            return false;
        }

        // 4. oszlop
        // Custom validation for lead user
        $leadUser = User::where('name', $row[3])->where('deleted', 0)->first();
        if (!$leadUser) {
            $this->errors[$index][] = "A(z) {$row[0]} költséghely témavezetője nem szerepel az adatbázis felhasználói között!";
            return false;
        }

        // Custom validation for lead user's group number matching the cost center group number
        if ($leadUser && $leadUser->workgroup && $leadUser->workgroup->workgroup_number !== $groupNumber) {
            $this->errors[$index][] = "A(z) {$row[0]} költséghely témavezetője nem a(z) {$groupNumber} számú csoport felhasználói között szerepel!";
            return false;
        }

        // 5. oszlop
        // Custom validation for project coordinator user
        $prCoordinatorUser = User::where('name', $row[4])->where('deleted', 0)->first();
        if (!$prCoordinatorUser) {
            $this->errors[$index][] = "A(z) {$row[0]} költséghely projektkoordinátora nem szerepel az adatbázis felhasználói között!";
            return false;
        }

        // Custom validation for project coordinator's group number being 910 or 911 (Column 5)
        if ($prCoordinatorUser && $prCoordinatorUser->workgroup && !in_array($prCoordinatorUser->workgroup->workgroup_number, [910, 911])) {
            $workgroupName = $prCoordinatorUser->workgroup->name; // Assuming workgroup has a name field
            $this->errors[$index][] = "A(z) {$row[0]} költséghely projektkoordinátora nem a 910 csoport {$workgroupName} vagy a 911 csoport {$workgroupName} felhasználói között szerepel!";
            return false;
        }

        return true;
    }

    /**
     * Define the validation messages for each rule.
     *
     * @return array
     */
    protected function validationMessages(): array
    {
        return [
            '0.required' => 'A költséghelyszámot mindegyik sorban szükséges megadni!',
            '0.regex' => 'A(z) :input költséghelyszám formátuma hibás!',
            '0.unique' => 'A(z) :input költséghely már rögzítve van az adattáblában!',
            '1.required' => 'A megnevezést mindegyik sorban szükséges megadni!',
            '1.max' => 'A(z) :input költséghely megnevezése túl hosszú, nem lehet több 255 karakternél!',
            '2.required' => 'A költséghely típusát mindegyik sorban szükséges megadni!',
            '3.required' => 'A témavezetőt mindegyik sorban szükséges megadni!',
            '4.required' => 'A projektkoordinátort mindegyik sorban szükséges megadni!',
            '5.date_format' => 'A(z) :input költséghely lejárata nem dátumformátumként (éééé.hh.nn vagy éééé-hh-nn) lett megadva!',
            '5.after_or_equal' => 'A(z) :input költséghely lejárata nem lehet a mai napnál korábbi dátum!',
        ];
    }

    /**
     * Validate the uploaded CSV file.
     *
     * @param UploadedFile $file
     * @return bool
     */
    protected function isValidCSV(UploadedFile $file): bool
    {
        // Check if the file is a valid text file
        $mimeType = $file->getMimeType();
        $content = file_get_contents($file->getRealPath());
        $lines = array_filter(explode("\n", $content)); // Filter out empty lines

        // Check if the content has semicolon (';') as the delimiter and line breaks ('\n')
        if ($mimeType !== 'text/plain' || strpos($content, ';') === false) {
            $this->errors[] = 'A feltölteni kívánt fájl formátuma nem megfelelő!';
            return false;
        }

        // Check that each line has exactly 6 columns
        foreach ($lines as $line) {
            $columns = explode(';', $line);
            if (count($columns) !== 6 && !empty(array_filter($columns))) {
                $this->errors[] = 'A feltölteni kívánt fájlnak 6 oszlopot kell tartalmaznia!';
                return false;
            }
        }

        return true;
    }

    /**
     * Parse the CSV file and return an array of rows.
     *
     * @param UploadedFile $file
     * @return array
     */
    protected function parseCSV(UploadedFile $file): array
    {
        $data = [];
        $handle = fopen($file->getRealPath(), 'r');

        while (($row = fgetcsv($handle, 1000, ';')) !== false) {
            // Convert each field to UTF-8 if necessary
            $row = array_map(function($field) {
                return mb_convert_encoding($field, 'UTF-8', 'ISO-8859-1'); // Convert to UTF-8
            }, $row);

            $data[] = $row; // Append to the data array
        }

        fclose($handle);

        return $data;
    }
}
