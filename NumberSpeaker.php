<?php
/**
 * Description of NumberSpeaker

 */
class System_View_Helper_NumberSpeaker {
    
    public $valueInWords;
    public $kwotaSlownie;
    public $kwota;
    public $pln;

    /**
     * Tablica opisów wartości jednostek.
     */
    private $Units = array
    (
        "Zero", "Jeden", "Dwa", "Trzy", "Cztery", "Pięć", "Sześć",
        "Siedem", "Osiem", "Dziewięć", "Dziesięć", "Jedenaście",
        "Dwanaście", "Trzynaście", "Czternaście", "Piętnaście",
        "Szesnaście", "Siedemnaście", "Osiemnaście", "Dziewiętnaście"
    );

    /**
     * Tablica opisów dziesiątek
     */
    private $Tens = array
    (
        "Dwadzieścia", "Trzydzieści", "Czterdzieści", "Pięćdziesiąt",
        "Sześćdziesiąt", "Siedemdziesiąt", "Osiemdziesiąt",
        "Dziewięćdziesiąt"
    );

    /**
     * Tablica obisów setek
     */
    private $Hundreds = array
    (
        "", "Sto", "Dwieście", "Trzysta", "Czterysta", "Pięćset",
        "Sześćset", "Siedemset", "Osiemset", "Dziewięćset"
    );

    /**
     * Dwuwymiarowa tablica tysięcy,milionów,miliarów
     */
    private $OtherUnits = array
    (
        array( "Tysiąc", "Tysiące", "Tysięcy"     ),
        array( "Milion", "Miliony", "Milionów"    ),
        array( "Miliard", "Miliardy", "Miliardów" )
    );

    /**
     * Constructor
     * @param $pln
     */
    public function  numberSpeaker($pln=null) {
       try {
            if(is_null($pln)){
                throw new Exception('Brak kwoty!');
            }
            $this->pln = sprintf('%03f', $pln);

            //polski format zapisu kwoty
            $this->kwota = number_format($pln, 2, ',', ' ');

            $kwota = $this->pln;
            $dkwota = 0;
            $dkwota = $kwota;

            $this->kwotaSlownie = $this->procKwotaSlownie($dkwota);
       } catch (Exception $e) {
            //Wyjątek błędu aplikacji
            $this->kwotaSlownie = "Bład, ".$e->getMessage();
       }
       
       return $this;
    }

    /**
     * SmallValueToWords
     *
     * @param Integer $n
     * @return String
     * Konwersja małych liczb
     */
    private function SmallValueToWords($n) {
        (int)$n = $n;
        if ($n == 0)
        {
            return null;
        }

        $this->valueInWords .= " ";
        // Konwertuj setki.
        $temp = $n / 100;
        if ($temp > 0)
        {
            $this->valueInWords .= $this->Hundreds[$temp];
            (int)$n -= (int)$temp * 100;
        }
        // Konwertuj dziesiątki i jedności.
        if ($n > 0)
        {
            if (strlen($this->valueInWords)> 0)
            {
                $this->valueInWords .= " ";
            }

            if ($n < 20)
            {
                //  Liczby poniżej 20 przekonwertuj na podstawie
                //  tablicy jedności.

                $this->valueInWords .= $this->Units[$n];
            }
            else
            {
                //  Większe liczby przekonwertuj łącząc nazwy
                //  krotności dziesiątek z nazwami jedności.
                $this->valueInWords .= $this->Tens[($n / 10) - 2];
                (int) $lastDigit = (int)$n % 10;

                if ((int)$lastDigit > 0)
                {
                    $this->valueInWords .= " ";
                    $this->valueInWords .= $this->Units[(int)$lastDigit];
                }
            }
        }
        return $this->valueInWords;
    }

    /**
     * StringBuilder
     * @param String $value
     * @return String
     *
     * Tworzy string znaków o pojemności 16 znaków.
     */
    private function stringBuilder($value="") {
        $ilosc_znakow = strlen($value);
        $ile_sp = (16 - $ilosc_znakow);
        $str = "";
        for($i = 1; $i<=$ile_sp; $i++){
           $str .= (string)" ";
        }
        return $value.$str;
    }

    /**
     * ToWordsString
     * @param String $value
     * @return String
     */
    private function ToWordsString($value) {
        if (!$value)
        {
            // Zero.
            return $this->Units[0];
        }

        $this->valueInWords = $this->stringBuilder($value);        
        $smallValue = $this->ToWords($this->valueInWords, $value, 0);

        if ($smallValue > 0)
        {
            if (strlen($this->valueInWords) > 0)
            {
                $this->valueInWords." ";
            }
            $this->valueInWords = $this->SmallValueToWords($smallValue);
        }
        return (string)$this->valueInWords;
    }

    /**
     * ToWords
     * @param String $valueInWords
     * @param String $n
     * @param Integer $level
     * @return Integer
     */
    private function ToWords($valueInWords, $n=0, $level=0)
    {
        $this->valueInWords = '';
        (int) $smallValue = 0;
        $divisor = pow(1000,$level+1);

        if ($divisor <= $n)
        {
            //  Jeśli liczbę da się podzielić przez najbliższą
            //  potęgę 1000, kontynuuj rekurencję.
            
            (int)$n = $this->ToWords($valueInWords, $n, $level+1);
            (int)$smallValue = (int)($n / $divisor);

            if (strlen($this->valueInWords) > 0)
            {
                $this->valueInWords .= " ";
            }

            if ($smallValue > 1)
            {
                $this->valueInWords = $this->SmallValueToWords($smallValue);
                $this->valueInWords .= " ";
            }

            $bigUnitIndex = $this->getBigUnitIndex($smallValue);
            $this->valueInWords .= $this->OtherUnits[$level][$bigUnitIndex];
        }
        return ($n - $smallValue * $divisor);
    }

    /**
     * GetBigUnitIndex
     * @param Integer $n
     * @return Integer
     *
     * Obliczenia dla dużych liczb i odmiana prawidłowa ich wartości
     */
    private function getBigUnitIndex($n)
    {
        $lastDigit = ($n % 10);
        if(($n >= 10 && ($n <= 20 || $lastDigit == 0)) || ($lastDigit > 4))
        {
            return 2;
        }elseif($lastDigit ==1 && $n > 20){
            return 2;
        }

        return ($lastDigit == 1) ? 0 : 1;
    }

    /**
     * liczbaZlotych
     * @param Integer $kwota
     * @return Integer
     */
    private function liczbaZlotych($kwota)
    {
//        $nkwota = number_format($kwota, 0, ',', '');
        $nkwota = (int)$kwota;        
        return $nkwota;
    }

    /**
     * liczbaGroszy
     * @param Integer $grosze
     * @return Integer
     */
    private function liczbaGroszy($grosze)
    {
        
        //Tworzę format zmiennych aby uzyskać liczbę w formie tekstowej
        $szlote = number_format($grosze, 2, '.', '');
        
        //Odcinam grosze
        $bgzlote = substr($szlote,0,strlen($szlote)-3);
        $dzlote = $bgzlote;
        //Od kowty z groszami odejmuję kwotę bez groszy.
        $groszy  = number_format($grosze*100 - $dzlote*100, 0, '', '');

        return $groszy;
    }

    /**
     * procKwotaSlownie
     * @param Double $kwota
     * @return String
     */
    private function procKwotaSlownie($kwota)
    {
        //Generalna funkcja przetworzenia zmiennej
        if ($kwota < 0)
        {
            $kwota = $kwota * -1;
        }

        $czlon1 = $this->ToWordsString($this->liczbaZlotych($kwota));
        $czlon2 = $this->ToWordsString($this->liczbaGroszy($kwota));

        $strKwotaSl = "";
        $strKwotaSl = $czlon1. " zł ". $czlon2." gr.";
        return $strKwotaSl;
    }

    public function __toString()
    {
        return $this->kwotaSlownie;
    }
}
?>