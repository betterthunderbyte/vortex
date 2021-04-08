<?php declare(strict_types=1); namespace core;
/**
 * MIT License
 *
 * Copyright (c) 2019 jeamu
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

class JsonWebToken
{
    private $secret;

    // header
    private $header;

    // payload
    private $payload;

    public function __construct()
    {
        $this->header = array(
          "alg" => "HS256",
          "typ" => "JWT"
        );

        $this->payload = array(

        );
    }

    public function setSecret(string $secret) : void
    {
        if(!isset($secret{4}))
        {
            throw new \Exception("Es muss ein sicherer Schlüsseln angegeben werden.");
        }

        $this->secret = $secret;
    }

    public function setIssuer(string $issuer) : void
    {
        $this->payload['iss'] = $issuer;
    }

    public function getIssuer() : string
    {
        return isset($this->payload['iss']) ? $this->payload['iss'] : '';
    }

    /**
     *
     * @param string $subject
     */
    public function setSubject(string $subject) : void
    {
        $this->payload["sub"] = $subject;
    }

    public function getSubject() : string
    {
        return isset($this->payload["sub"]) ? $this->payload["sub"] : '';
    }

    public function setAudience(string $audience) : void
    {
        $this->payload['aud'] = $audience;
    }

    public function getAudience() : string
    {
        return isset($this->payload['aud']) ? $this->payload['aud'] : '';
    }

    public function setExpirationTime(int $expiration_time) : void
    {
        $this->payload['exp'] = $expiration_time;
    }

    public function getExpirationTime() : int
    {
        return isset($this->payload['exp']) ? $this->payload['exp'] : 0;
    }

    public function setNotBefore(int $time) : void
    {
        $this->payload['nbf'] = $time;
    }

    public function getNotBefore() : int
    {
        return isset($this->payload['nbf']) ? $this->payload['nbf'] : 0;
    }

    public function setIssuedAt(int $time) : void
    {
        $this->payload['iat'] = $time;
    }

    public function getIssuedAt() : int
    {
        return isset($this->payload['iat']) ? $this->payload['iat'] : 0;
    }

    public function setJWTID(string $jti) : void
    {
        $this->payload['jti'] = $jti;
    }

    public function getJWT() : string
    {
        return isset($this->payload['jti']) ? $this->payload['jti'] : '';
    }

    public function setBool(string $key, bool $value) : void
    {
        $this->payload[$key] = $value;
    }

    public function setString(string $key, string $value) : void
    {
        $this->payload[$key] = $value;
    }

    public function getBool(string $key, bool $default = false) : bool
    {
        if(isset($this->payload[$key]))
        {
            return (bool)$this->payload[$key];
        }

        return $default;
    }

    public function getString(string $key, string $default = '') : string
    {
        if(isset($this->payload[$key]))
        {
            return (string)$this->payload[$key];
        }

        return $default;
    }

    public function setPayloadValue(string $key, string $value) : void
    {
        $this->payload[$key] = $value;
    }

    public function getPayloadValue(string $key, & $value) : bool
    {
        if(isset($this->payload[$key]))
        {
            $value = $this->payload[$key];
            return true;
        }

        return false;
    }

    private function headerToBase64() : string
    {
        return Tool::base64urlEncode(Tool::jsonEncode($this->header));
    }

    private function payloadToBase64() : string
    {
        return Tool::base64urlEncode(Tool::jsonEncode($this->payload));
    }

    // Generiert aus den den Daten einen gültigen JsonWebToken
    public function export() : string
    {
        $header_hash = $this->headerToBase64();
        $payload_hash = $this->payloadToBase64();

        $together = $header_hash . '.' . $payload_hash;

        $hash = hash_hmac("sha256", $together, $this->secret);

        return $together . '.' . Tool::base64urlEncode($hash);
    }

    public function fromJsonToken(string $token) : void
    {
        if($this->validateJsonToken($token))
        {
            $parts = explode(".", $token);
            $header = $parts[0];
            $payload = $parts[1];
            $hash = $parts[2];

            $header = Tool::jsonDecode(Tool::base64urlDecode($header));

            if(!Tool::issetArray($header, array(
                "alg",
                "typ"
            )))
            {
                throw new \Exception("");
            }

            $this->payload = Tool::jsonDecode(Tool::base64urlDecode($payload));
        }
    }

    public function validateJsonToken(string $token) : bool
    {
        $parts = explode(".", $token);
        if(count($parts) != 3)
        {
            return false;
        }

        $header = $parts[0];
        $payload = $parts[1];
        $hash = Tool::base64urlDecode($parts[2]);
        $current_hash = hash_hmac("sha256", $header . '.' . $payload, $this->secret);
        if($current_hash != $hash)
        {
            return false;
        }

        $payload = Tool::jsonDecode(Tool::base64urlDecode($payload));

        if(isset($payload["exp"]))
        {
            try
            {
                $expire = $payload["exp"];
                if($expire < time())
                {
                    return false;
                }
            }
            catch (\Exception $e)
            {
                return false;
            }
        }
        else
        {
            return false;
        }

        return true;
    }

}
