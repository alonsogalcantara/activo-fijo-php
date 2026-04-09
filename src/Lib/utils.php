<?php

/**
 * Utility functions for formatting data in the Activo Fijo system.
 */

class Utils {
    /**
     * Formats a given number into currency (Mexican Peso - MXN).
     * 
     * @param float|int $amount The amount to format.
     * @param bool $includeSymbol Whether to include the $ symbol.
     * @return string The formatted currency.
     */
    public static function formatCurrency($amount, $includeSymbol = true): string {
        if (!is_numeric($amount)) {
            $amount = 0;
        }
        $formatted = number_format((float)$amount, 2, '.', ',');
        return $includeSymbol ? '$ ' . $formatted : $formatted;
    }

    /**
     * Formats a date string (or timestamp) to a standard Mexican format (DD/MM/YYYY).
     *
     * @param string|int $date Date string or timestamp.
     * @param bool $includeTime Whether to include hours and minutes.
     * @return string The formatted date.
     */
    public static function formatDate($date, $includeTime = false): string {
        if (empty($date)) {
            return 'N/D';
        }
        
        $timestamp = is_numeric($date) ? (int)$date : strtotime($date);
        
        if (!$timestamp) {
            return 'Fecha inválida';
        }

        $format = $includeTime ? 'd/m/Y H:i:s' : 'd/m/Y';
        return date($format, $timestamp);
    }
    


    /**
     * Generates a reusable HTML table for the system.
     *
     * @param array $headers Associative array of column key => 'Header Label'
     * @param array $data Array of associative arrays containing the row data
     * @param string $emptyMessage Message to show when data is empty
     * @param array $actions Optional actions to include per row (e.g. edit, delete)
     * @return string The generated HTML table snippet
     */
    public static function generateTable(array $headers, array $data, string $emptyMessage = "No hay registros.", array $actions = [], string $tableId = ''): string {
        $html = '<div class="bg-white rounded-xl shadow overflow-hidden w-full">';
        $html .= '<div class="overflow-x-auto">';
        $idAttr = $tableId !== '' ? ' id="' . htmlspecialchars($tableId) . '"' : '';
        $html .= '<table class="w-full text-left border-collapse"' . $idAttr . '>';
        $html .= '<thead class="bg-gray-800 text-white"><tr>';
        
        $colIndex = 0;
        foreach ($headers as $key => $headerConfig) {
            $label = is_array($headerConfig) ? ($headerConfig['label'] ?? '') : $headerConfig;
            $alignClass = is_array($headerConfig) && isset($headerConfig['align']) ? ' text-' . $headerConfig['align'] : '';
            // TableManager (search_bar.php) securely binds cursors and clicks natively via DOM. No inline onclick needed.
            $html .= '<th class="p-4 text-sm font-semibold tracking-wide cursor-pointer hover:bg-gray-700 transition' . $alignClass . '">' . htmlspecialchars($label) . '</th>';
            $colIndex++;
        }
        if (!empty($actions)) {
            $html .= '<th class="p-4 text-sm font-semibold tracking-wide text-center">Acciones</th>';
        }
        $html .= '</tr></thead>';
        $html .= '<tbody class="divide-y divide-gray-100" id="' . htmlspecialchars($tableId) . 'Body">';

        if (empty($data)) {
            $colspan = count($headers) + (!empty($actions) ? 1 : 0);
            $html .= '<tr><td colspan="' . $colspan . '" class="p-8 text-center text-gray-500 italic bg-gray-50 rounded-b-xl">' . htmlspecialchars($emptyMessage) . '</td></tr>';
        } else {
            foreach ($data as $row) {
                // extra classes and attributes for <tr>
                $rowClass = 'hover:bg-gray-50 transition duration-150 group';
                if (!empty($row['_rowClass'])) {
                    $rowClass .= ' ' . $row['_rowClass'];
                }
                $rowAttrs = '';
                if (!empty($row['_rowAttrs'])) {
                    $rowAttrs = ' ' . $row['_rowAttrs'];
                }

                $html .= '<tr class="' . htmlspecialchars($rowClass) . '"' . $rowAttrs . '>';
                
                // Allow generating td raw attributes
                foreach ($headers as $key => $headerConfig) {
                    $label = is_array($headerConfig) ? ($headerConfig['label'] ?? '') : $headerConfig;
                    $alignClass = is_array($headerConfig) && isset($headerConfig['align']) ? ' text-' . $headerConfig['align'] : '';
                    $val = $row[$key] ?? '';
                    
                    $tdClass = 'p-4' . $alignClass;
                    if (isset($row["_tdClass_$key"])) {
                        $tdClass .= ' ' . $row["_tdClass_$key"];
                    }
                    $tdAttrs = '';
                    if (isset($row["_tdAttrs_$key"])) {
                        $tdAttrs = ' ' . $row["_tdAttrs_$key"];
                    }
                    
                    // Values are assumed to be safe or pre-escaped by the caller to allow HTML composition
                    $html .= '<td class="' . htmlspecialchars($tdClass) . '" data-label="' . htmlspecialchars($label) . '"' . $tdAttrs . '>' . $val . '</td>';
                }
                
                if (!empty($actions)) {
                     $html .= '<td data-label="Acciones" class="p-4 text-center">';
                     $html .= '<div class="flex justify-center gap-2">';
                     foreach ($actions as $action) {
                         $idVal = $row['id'] ?? ($row[array_key_first($row)] ?? '');
                         $url = isset($action['url']) ? str_replace('{id}', $idVal, $action['url']) : '#';
                         $icon = $action['icon'] ?? 'fas fa-cog';
                         $colorClass = $action['colorClass'] ?? 'text-gray-600';
                         $bgHover = $action['bgHover'] ?? 'hover:bg-gray-50';
                         $border = $action['border'] ?? 'border border-gray-200';
                         $title = $action['title'] ?? '';
                         
                         $onclick = '';
                         if (!empty($action['confirm'])) {
                             $onclick = ' onclick="return confirm(\'' . htmlspecialchars($action['confirm']) . '\')"';
                         }
                         
                         $html .= '<a href="' . htmlspecialchars($url) . '"' . $onclick . ' class="' . htmlspecialchars($colorClass . ' ' . $bgHover . ' ' . $border) . ' p-2 rounded transition" title="' . htmlspecialchars($title) . '">';
                         $html .= '<i class="' . htmlspecialchars($icon) . '"></i>';
                         $html .= '</a>';
                     }
                     $html .= '</div></td>';
                }
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table></div></div>';
        return $html;
    }
}
