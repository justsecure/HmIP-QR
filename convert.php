<?php

function baseConvertCustom(
    string $input,
    string $alphabetFrom,
    string $alphabetTo
): string {
    $collect = [];
    $baseFrom = strlen($alphabetFrom);
    $baseTo   = strlen($alphabetTo);

    for ($j = 0; $j < strlen($input); $j++) {
        $tmp = strpos($alphabetFrom, $input[$j]);

        if ($tmp === false) {
            throw new InvalidArgumentException(
                "Ungültiges Zeichen: " . $input[$j]
            );
        }

        $i = 0;

        do {
            $current = $collect[$i] ?? 0;
            $next = ($current * $baseFrom) + $tmp;

            $collect[$i] = $next % $baseTo;
            $tmp = intdiv($next, $baseTo);
            $i++;
        } while (!($i === count($collect) && $tmp === 0));
    }

    $result = '';

    for ($j = count($collect) - 1; $j >= 0; $j--) {
        $result .= $alphabetTo[$collect[$j]];
    }

    return $result;
}

function addHyphens(
    string $input,
    int $characterCount
): string {
    return implode('-', str_split($input, $characterCount));
}

function formatKey(string $key): string
{
    // Beim Basiswechsel verlorene führende Nullen ergänzen
    $key = str_pad($key, 26, '0', STR_PAD_LEFT);

    // Gruppierung: 5 + 5 + 5 + 5 + 6
    return implode('-', [
        substr($key, 0, 5),
        substr($key, 5, 5),
        substr($key, 10, 5),
        substr($key, 15, 5),
        substr($key, 20, 6)
    ]);
}

function convertHomematicCode(string $value): array
{
    $value = strtoupper(trim($value));

    $pattern = '/^(\w{6})(\w{24})(\w{3})(\w{32})$/';

    if (!preg_match($pattern, $value, $groups)) {
        throw new InvalidArgumentException('Eingabe ungültig');
    }

    $sgtin = addHyphens($groups[2], 4);

    $convertedKey = baseConvertCustom(
        $groups[4],
        '0123456789ABCDEF',
        '0123456789ABCEFGHJKLMNPQRSTUWXYZ'
    );

    return [
        'sgtin' => $sgtin,
        'key'   => formatKey($convertedKey)
    ];
}


try {
    $result = convertHomematicCode($_GET['code'] ?? '');

    echo 'SGTIN: ' . htmlspecialchars($result['sgtin']) . '<br>';
    echo 'Key: ' . htmlspecialchars($result['key']);
} catch (InvalidArgumentException $exception) {
    echo htmlspecialchars($exception->getMessage());
}
