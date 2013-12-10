<?php

class BadPasswordInputException extends RuntimeException {}

class PasswordInput extends CheckBase
{
    var $currentPassword;

    var $password;

    var $new_word1;

    var $new_word2;

    var $min_word = 10;

    /**
     * @param string $password
     */
    public function setCurrentPassword( $password )
    {
        $this->currentPassword = $password;
    }

    /**
     * @throws BadPasswordInputException
     * @return int
     */
    public function check()
    {
        $password = $this->pgg->pushChar( $this->password,  PGG_VALUE_MUST_EXIST, '[-_a-zA-Z0-9]*' );
        $new1     = $this->pgg->pushChar( $this->new_word1, PGG_VALUE_MUST_EXIST, '[-_a-zA-Z0-9]*' );
        $new2     = $this->pgg->pushChar( $this->new_word2, PGG_VALUE_MUST_EXIST, '[-_a-zA-Z0-9]*' );

        if( !$password ) {
            throw new BadPasswordInputException( '現在のパスワード（英数字8文字）を入力してください' );
        }
        if( !$this->currentPassword ) {
            throw new BadPasswordInputException( '現在のパスワードを設定してください。' );
        }
        if( $password != $this->currentPassword ) {
            throw new BadPasswordInputException( 'パスワードが違います。' );
        }
        if( !$new1 || !$new2 ) {
            throw new BadPasswordInputException( '新しいパスワード（英数字8文字）を両方に入力してください' );
        }
        if( $new1 != $new2 ) {
            throw new BadPasswordInputException( 'パスワードが異なります' );
        }
        if( strlen( $new1 ) < $this->min_word ) {
            throw new BadPasswordInputException( "新しいパスワードを英数字{$this->min_word}文字以上で入力してください" );
        }
        $this->pgg->all_variables = array(
            $this->password => $new1,
        );
    }
}