@extends('dcat-wechat-open-platform::errors.minimal')
@section('message', 'ERROR: ' . __($exception->getMessage() ?: '400 ERROR'))
