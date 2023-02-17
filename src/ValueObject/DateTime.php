<?php declare(strict_types=1);

namespace Movary\ValueObject;

use DateTimeZone;
use JsonSerializable;

class DateTime implements JsonSerializable
{
    private const DEFAULT_STRING_FORMAT = 'Y-m-d H:i:s';

    private const DEFAULT_TIME_ZONE = 'UTC';

    private const STATE_FORMAT = 'Y-m-d H:i:s.u';

    private string $dateTime;

    private function __construct(\DateTime $dateTime)
    {
        $this->dateTime = $dateTime->format(self::STATE_FORMAT);
    }

    public static function create() : self
    {
        return self::createFromString('now');
    }

    public static function createFromString(string $dateTimeString) : self
    {
        return new self(new \DateTime($dateTimeString, new DateTimeZone(self::DEFAULT_TIME_ZONE)));
    }

    public static function createFromStringAndTimeZone(string $dateTimeString, string $timeZone) : self
    {
        return new self(new \DateTime($dateTimeString, new DateTimeZone($timeZone)));
    }

    public static function createFromFormatAndTimestamp(string $format, string $timestamp) : self
    {
        return new self(\DateTime::createFromFormat($format, $timestamp));
    }

    public function __toString() : string
    {
        return $this->format(self::DEFAULT_STRING_FORMAT);
    }

    public function differenceInHours(DateTime $dateTime) : int
    {
        return (int)(((new \DateTime($this->dateTime))->getTimestamp() - (new \DateTime((string)$dateTime))->getTimestamp()) / 60 / 60);
    }

    public function format(string $format) : string
    {
        return (new \DateTime($this->dateTime))->format($format);
    }

    public function isAfter(DateTime $dateTimeToCompare) : bool
    {
        return $this->dateTime > $dateTimeToCompare->dateTime;
    }

    public function isEqual(DateTime $lastUpdated) : bool
    {
        return (string)$this === (string)$lastUpdated;
    }

    public function jsonSerialize() : string
    {
        return $this->format(self::DEFAULT_STRING_FORMAT);
    }
}
