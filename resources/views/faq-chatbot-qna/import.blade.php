@extends('layouts.app')

@section('title', 'Import FAQ Chatbot QnA')
@section('page-title', 'Import FAQ Chatbot QnA')

@section('content')
  <x-import-form :action="route('faq-chatbot-qna.import.store')" :templateRoute="route('faq-chatbot-qna.import.template')" title="Import FAQ Chatbot QnA" />
@endsection


