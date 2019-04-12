<?php declare(strict_types=1);


namespace Swoft\Log\Handler;


use Monolog\Handler\AbstractProcessingHandler;
use Swoft\Co;

/**
 * Class FileHandler
 *
 * @since 2.0
 */
class FileHandler extends AbstractProcessingHandler
{
    /**
     * Write log levels
     *
     * @var array
     */
    protected $levels = [];

    /**
     * Write log file
     *
     * @var string
     */
    protected $logFile = '';

    /**
     * Will exec on construct
     */
    public function init(): void
    {
        $this->logFile = \alias($this->logFile);
        $this->createDir();
    }

    /**
     * Write log by batch
     *
     * @param array $records
     *
     * @return void
     */
    public function handleBatch(array $records): void
    {
        $records = $this->recordFilter($records);
        if (!$records) {
            return;
        }

        $lines = \array_column($records, 'formatted');

        $this->write($lines);
    }

    /**
     * Write file
     *
     * @param array $records
     */
    protected function write(array $records): void
    {
        $messageText = \implode("\n", $records) . "\n";

        if (Co::id() <= 0) {
            throw new \InvalidArgumentException('Write log file must be under Coroutine!');
        }

        $res = Co::writeFile($this->logFile, $messageText, FILE_APPEND);

        if ($res === false) {
            throw new \InvalidArgumentException(
                sprintf('Unable to append to log file: %s', $this->logFile)
            );
        }
    }

    /**
     * Filter record
     *
     * @param array $records
     *
     * @return array
     */
    private function recordFilter(array $records): array
    {
        $messages = [];
        foreach ($records as $record) {
            if (!isset($record['level'])) {
                continue;
            }
            if (!$this->isHandling($record)) {
                continue;
            }

            $record              = $this->processRecord($record);
            $record['formatted'] = $this->getFormatter()->format($record);

            $messages[] = $record;
        }
        return $messages;
    }

    /**
     * Create dir
     */
    private function createDir(): void
    {
        $logDir = \dirname($this->logFile);

        if ($logDir !== null && !\is_dir($logDir)) {
            $status = mkdir($logDir, 0777, true);
            if ($status === false) {
                throw new \UnexpectedValueException(
                    sprintf('There is no existing directory at "%s" and its not buildable: ', $logDir)
                );
            }
        }
    }

    /**
     * Whether to handler log
     *
     * @param array $record
     *
     * @return bool
     */
    public function isHandling(array $record): bool
    {
        if ($this->levels) {
            return true;
        }

        return \in_array($record['level'], $this->levels, true);
    }
}