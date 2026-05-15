<?php declare(strict_types=1);
// PHP Validator Class, a simple, fluent style, zero-dependency data Validator.
// See: https://github.com/devwithkunal/php-validator-class
//
// Copyright (C) 2023 Kunal Ali Khan
// Copyright (C) 2026 Simon Dann
//
//  Permission is hereby granted, free of charge, to any person obtaining a copy
//  of this software and associated documentation files (the "Software"), to deal
//  in the Software without restriction, including without limitation the rights
//  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
//  copies of the Software, and to permit persons to whom the Software is
//  furnished to do so, subject to the following conditions:
//
//  The above copyright notice and this permission notice shall be included in all
//  copies or substantial portions of the Software.
//
//  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
//  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
//  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
//  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
//  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
//  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
//  SOFTWARE.
//
// File: src/Services/Validator.php

namespace BlackNova\Services;

use DateTime;

class Validator
{
    /**
     * @var array $data Data to validate.
     */
    private array $data;

    /**
     * @var string $current_field Current selected key/field to validae data.
     */
    private string $current_field;

    /**
     * @var string $current_alias Alias use on error messages instead of field name.
     */
    private string|null $current_alias;

    /**
     * @var array $response_messages Error messages to show user.
     *
     * You can change messages from here. User "{field}" to refer the field name.
     */
    private array $response_messages = [
        "required" => "{field} is required.",
        "alpha" => "{field} must contains alphabetic characters only.",
        "alpha_num" => "{field} must contains alphabetic characters & numbers only.",
        "numeric" => "{field} must contains numbers only.",
        "email" => "{field} is invalid.",
        "max_len" => "{field} is too long.",
        "min_len" => "{field} is too short.",
        "max_val" => "{field} is too high.",
        "min_val" => "{field} is too low.",
        "enum" => "{field} is invalid.",
        "equals" => "{field} does not match.",
        "must_contain" => "{field} must contains {chars}.",
        "match" => "{field} is invalid.",
        "date" => "{field} is invalid.",
        "date_after" => "{field} date is not valid.",
        "date_before" => "{field} date is not valid.",
    ];

    /**
     * @var array $error_messages Error message generated after validation of each field.
     */
    public array $error_messages = [];

    /**
     * @var boolean $next Check if next validation on the field shoud run or not.
     */
    private bool $next = true;

    /**
     * Validator Create a new instance of Validator class.
     *
     * @param array $data Data to validate.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * add_error_message - Create and add an error message after each validation failed.
     *
     * @param string $type Key of $response_messages array.
     * @param array $others
     * @return void
     */
    private function add_error_message(string $type, array $others = []): void
    {
        $field_name = $this->current_alias ? ucfirst($this->current_alias) : ucfirst($this->current_field);
        $msg = str_replace('{field}', $field_name, $this->response_messages[$type]);
        foreach ($others as $key => $val) {
            $msg = str_replace('{' . $key . '}', $val, $msg);
        }
        $this->error_messages[$this->current_field] = $msg;
    }

    /**
     * exists - Check if the current field or field value exists or not.
     *
     * @return boolean
     */
    private function exists(): bool
    {
        if (!isset($this->data[$this->current_field]) || !$this->data[$this->current_field]) {
            return false;
        }
        return true;
    }

    /**
     * set_response_messages - Function to set/extend custom error response messages.
     *
     * @param array $messages
     * @return void
     */
    public function set_response_messages(array $messages): void
    {
        foreach ($messages as $key => $val) {
            $this->response_messages[$key] = $val;
        }
    }

    /**
     * field - Set the field name to start validation.
     *
     * @param string $name Name of the field/key as on data to validate.
     * @param string|null $alias (optional) Alias use on error messages instead of field name.
     * @return self
     */
    public function field(string $name, ?string $alias = null): self
    {
        $this->current_field = $name;
        $this->next = true;
        $this->current_alias = $alias;
        return $this;
    }

    /**
     * required - Check if the value exists.
     *
     * @return self
     */
    public function required(): self
    {
        if (!$this->exists()) {
            $this->add_error_message('required');
            $this->next = false;
        }
        return $this;
    }

    /**
     * alpha - Check if the value is alpha only.
     *
     * @param array $ignore (Optional) add charectors to allow.
     * @return self
     */
    public function alpha(array $ignore = []): self
    {
        if ($this->next && $this->exists() && !ctype_alpha(str_replace($ignore, '', $this->data[$this->current_field]))) {
            $this->add_error_message('alpha');
            $this->next = false;
        }
        return $this;
    }

    /**
     * alpha_num - Check if the value is alphanumeric only.
     *
     * @param array $ignore (Optional) add charectors to allow.
     * @return self
     */
    public function alpha_num(array $ignore = []): self
    {
        if ($this->next && $this->exists() && !ctype_alnum(str_replace($ignore, '', $this->data[$this->current_field]))) {
            $this->add_error_message('alpha_num');
            $this->next = false;
        }
        return $this;
    }

    /**
     * numeric - Check if the value is numeric only.
     *
     * @return self
     */
    public function numeric(): self
    {
        if ($this->next && $this->exists() && !is_numeric($this->data[$this->current_field])) {
            $this->add_error_message('numeric');
            $this->next = false;
        }
        return $this;
    }

    /**
     * email - Check if the value is a valid email.
     *
     * @return self
     */
    public function email(): self
    {
        if ($this->next && $this->exists() && !filter_var($this->data[$this->current_field], FILTER_VALIDATE_EMAIL)) {
            $this->add_error_message('email');
            $this->next = false;
        }
        return $this;
    }

    /**
     * max_len - Check if the length of the value is larger than the limit.
     *
     * @param int $size Max length of charectors of the value.
     * @return self
     */
    public function max_len(int $size): self
    {
        if ($this->next && $this->exists() && strlen($this->data[$this->current_field]) > $size) {
            $this->add_error_message('max_len');
            $this->next = false;
        }
        return $this;
    }

    /**
     * min_len - Check if the length of the value is smaller than the limit.
     *
     * @param int $size Min length of charectors of the value.
     * @return self
     */
    public function min_len(int $size): self
    {
        if ($this->next && $this->exists() && strlen($this->data[$this->current_field]) < $size) {
            $this->add_error_message('min_len');
            $this->next = false;
        }
        return $this;
    }

    /**
     * max_val - Check if the value of intiger/number is not larger than the limit.
     *
     * @param int $val Max value of the number.
     * @return self
     */
    public function max_val(int $val): self
    {
        if ($this->next && $this->exists() && $this->data[$this->current_field] > $val) {
            $this->add_error_message('max_val');
            $this->next = false;
        }
        return $this;
    }

    /**
     * min_val - Check if the value of intiger/number is not smaller than the limit.
     *
     * @param int $val Min value of the number.
     * @return self
     */
    public function min_val(int $val): self
    {
        if ($this->next && $this->exists() && $this->data[$this->current_field] < $val) {
            $this->add_error_message('min_val');
            $this->next = false;
        }
        return $this;
    }

    /**
     * enum - Check if the value is in the list.
     *
     * @param array $list List of valid values.
     * @return self
     */
    public function enum(array $list): self
    {
        if ($this->next && $this->exists() && !in_array($this->data[$this->current_field], $list)) {
            $this->add_error_message('enum');
            $this->next = false;
        }
        return $this;
    }

    /**
     * equals - Check if the value is equal.
     *
     * @param mixed $value Value to match equal.
     * @return self
     */
    public function equals(mixed $value): self
    {
        if ($this->next && $this->exists() && !$this->data[$this->current_field] == $value) {
            $this->add_error_message('equals');
            $this->next = false;
        }
        return $this;
    }

    /**
     * date - Check if the value is a valid date.
     *
     * @param mixed $format format of the date. (ex. Y-m-d) Check out https://www.php.net/manual/en/datetime.format.php for more.
     * @return self
     */
    public function date(string $format = 'Y-m-d'): self
    {
        if ($this->next && $this->exists()) {
            $dateTime = DateTime::createFromFormat($format, $this->data[$this->current_field]);
            if (!($dateTime && $dateTime->format($format) == $this->data[$this->current_field])) {
                $this->add_error_message('date');
                $this->next = false;
            }
        }
        return $this;
    }

    /**
     * date_after - Check if the date appeared after the specified date.
     *
     * @param mixed $date Use format Y-m-d (ex. 2023-01-15).
     * @return self
     */
    public function date_after(string $date): self
    {
        if ($this->next && $this->exists() && strtotime($date) >= strtotime($this->data[$this->current_field])) {
            $this->add_error_message('date_after');
            $this->next = false;
        }
        return $this;
    }

    /**
     * date_before - Check if the date appeared before the specified date.
     *
     * @param mixed $date Use format Y-m-d (ex. 2023-01-15).
     * @return self
     */
    public function date_before(string $date): self
    {
        if ($this->next && $this->exists() && strtotime($date) <= strtotime($this->data[$this->current_field])) {
            $this->add_error_message('date_before');
            $this->next = false;
        }
        return $this;
    }

    /**
     * must_contain - Check if the value must contain some characters.
     *
     * @param string $chars Set of chars in one string ex. "@#$&abc123".
     * @return self
     */
    public function must_contain(string $chars): self
    {
        if ($this->next && $this->exists() && !preg_match("/[" . $chars . "]/i", $this->data[$this->current_field])) {
            $this->add_error_message('must_contain', ['chars' => $chars]);
            $this->next = false;
        }
        return $this;
    }

    /**
     * match - Check if the value matches a pattern.
     *
     * @param string $patarn Rejex pattern to match.
     * @return self
     */
    public function match(string $patarn): self
    {
        if ($this->next && $this->exists() && !preg_match($patarn, $this->data[$this->current_field])) {
            $this->add_error_message('match');
            $this->next = false;
        }
        return $this;
    }

    /**
     * is_valid - Check if all validations is successfull.
     *
     * @return boolean
     */
    public function is_valid(): bool
    {
        return count($this->error_messages) == 0;
    }
}
