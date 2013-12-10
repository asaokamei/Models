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
            throw new BadPasswordInputException( '���ߤΥѥ���ɡʱѿ���8ʸ���ˤ����Ϥ��Ƥ�������' );
        }
        if( !$this->currentPassword ) {
            throw new BadPasswordInputException( '���ߤΥѥ���ɤ����ꤷ�Ƥ���������' );
        }
        if( $password != $this->currentPassword ) {
            throw new BadPasswordInputException( '�ѥ���ɤ��㤤�ޤ���' );
        }
        if( !$new1 || !$new2 ) {
            throw new BadPasswordInputException( '�������ѥ���ɡʱѿ���8ʸ���ˤ�ξ�������Ϥ��Ƥ�������' );
        }
        if( $new1 != $new2 ) {
            throw new BadPasswordInputException( '�ѥ���ɤ��ۤʤ�ޤ�' );
        }
        if( strlen( $new1 ) < $this->min_word ) {
            throw new BadPasswordInputException( "�������ѥ���ɤ�ѿ���{$this->min_word}ʸ���ʾ�����Ϥ��Ƥ�������" );
        }
        $this->pgg->all_variables = array(
            $this->password => $new1,
        );
    }
}