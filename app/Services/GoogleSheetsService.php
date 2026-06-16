<?php

namespace App\Services;

use Google_Client;

class GoogleSheetsService
{
    protected $client;
    protected $service;
    protected $credentialsPath;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->credentialsPath = storage_path('app/google/credentials-google.json');
        $this->client->setAuthConfig($this->credentialsPath); // salve aqui o JSON da service account
        $this->client->addScope(\Google\Service\Sheets::SPREADSHEETS);

        $this->service = new \Google\Service\Sheets($this->client);
    }

    public function readSheet($spreadsheetId, $range)
    {
        $response = $this->service->spreadsheets_values->get($spreadsheetId, $range);
        return $response->getValues();
    }

    public function updateSheet($spreadsheetId, $range, array $values, $valueInputOption = 'USER_ENTERED')
    {
        $body = new \Google\Service\Sheets\ValueRange([
            'values' => $values,
        ]);

        $params = [
            'valueInputOption' => $valueInputOption,
        ];

        return $this->service->spreadsheets_values->update($spreadsheetId, $range, $body, $params);
    }

    public function appendSheet($spreadsheetId, $range, array $values, $valueInputOption = 'USER_ENTERED')
    {
        $body = new \Google\Service\Sheets\ValueRange([
            'values' => $values,
        ]);

        $params = [
            'valueInputOption' => $valueInputOption,
            'insertDataOption' => 'INSERT_ROWS',
        ];

        return $this->service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
    }

    public function batchUpdateValues($spreadsheetId, array $updates, $valueInputOption = 'USER_ENTERED')
    {
        $data = [];

        foreach ($updates as $update) {
            $data[] = new \Google\Service\Sheets\ValueRange([
                'range' => $update['range'],
                'values' => $update['values'],
            ]);
        }

        $body = new \Google\Service\Sheets\BatchUpdateValuesRequest([
            'valueInputOption' => $valueInputOption,
            'data' => $data,
        ]);

        return $this->service->spreadsheets_values->batchUpdate($spreadsheetId, $body);
    }

    public function clearRange($spreadsheetId, $range)
    {
        $body = new \Google\Service\Sheets\ClearValuesRequest();
        return $this->service->spreadsheets_values->clear($spreadsheetId, $range, $body);
    }

    public function getSpreadsheet($spreadsheetId)
    {
        return $this->service->spreadsheets->get($spreadsheetId);
    }

    public function getSheetIdByTitle($spreadsheetId, string $title): ?int
    {
        $spreadsheet = $this->getSpreadsheet($spreadsheetId);
        $sheets = $spreadsheet->getSheets() ?? [];

        foreach ($sheets as $sheet) {
            $properties = $sheet->getProperties();
            if (!$properties) {
                continue;
            }
            if ((string) $properties->getTitle() === $title) {
                return (int) $properties->getSheetId();
            }
        }

        return null;
    }

    public function deleteSheetByTitle($spreadsheetId, string $title): bool
    {
        $sheetId = $this->getSheetIdByTitle($spreadsheetId, $title);
        if ($sheetId === null) {
            return false;
        }

        $request = new \Google\Service\Sheets\Request([
            'deleteSheet' => [
                'sheetId' => $sheetId,
            ],
        ]);

        $batch = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
            'requests' => [$request],
        ]);

        $this->service->spreadsheets->batchUpdate($spreadsheetId, $batch);
        return true;
    }

    public function duplicateSheet($spreadsheetId, string $sourceTitle, string $newTitle): int
    {
        $sourceId = $this->getSheetIdByTitle($spreadsheetId, $sourceTitle);
        if ($sourceId === null) {
            throw new \RuntimeException("Aba de origem não encontrada: {$sourceTitle}");
        }

        $existing = $this->getSheetIdByTitle($spreadsheetId, $newTitle);
        if ($existing !== null) {
            $this->deleteSheetByTitle($spreadsheetId, $newTitle);
        }

        $request = new \Google\Service\Sheets\Request([
            'duplicateSheet' => [
                'sourceSheetId' => $sourceId,
                'newSheetName' => $newTitle,
            ],
        ]);

        $batch = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
            'requests' => [$request],
        ]);

        $response = $this->service->spreadsheets->batchUpdate($spreadsheetId, $batch);
        $replies = $response->getReplies() ?? [];
        $reply = $replies[0] ?? null;

        $properties = $reply && method_exists($reply, 'getDuplicateSheet') ? $reply->getDuplicateSheet()?->getProperties() : null;
        $newSheetId = $properties ? $properties->getSheetId() : null;

        if ($newSheetId === null) {
            $newSheetId = $this->getSheetIdByTitle($spreadsheetId, $newTitle);
        }

        if ($newSheetId === null) {
            throw new \RuntimeException("Falha ao duplicar aba para: {$newTitle}");
        }

        return (int) $newSheetId;
    }
    
    

    public function getServiceAccountEmail(): ?string
    {
        try {
            if (empty($this->credentialsPath) || !is_string($this->credentialsPath) || !file_exists($this->credentialsPath)) {
                return null;
            }

            $raw = file_get_contents($this->credentialsPath);
            if ($raw === false) {
                return null;
            }

            $json = json_decode($raw, true);
            if (!is_array($json)) {
                return null;
            }

            $email = $json['client_email'] ?? null;
            if (!is_string($email) || trim($email) === '') {
                return null;
            }

            return trim($email);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
