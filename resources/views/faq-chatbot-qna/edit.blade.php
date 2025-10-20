@extends('layouts.app')

@section('title', 'Edit FAQ')
@section('page-title', 'Edit FAQ')

@section('content')
  <x-card>
    <div class="p-6">
      <form method="POST" action="{{ route('faq-chatbot-qna.update', $faq->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6">
          <x-input name="question" label="Question" :value="old('question', $faq->question)" placeholder="Type question" required />
          <x-input-error :messages="$errors->get('question')" />

          <x-textarea name="answer" label="Answer" :value="old('answer', $faq->answer)" placeholder="Type answer" :rows="5" required />
          <x-input-error :messages="$errors->get('answer')" />
        </div>

        <div class="flex items-center justify-end space-x-3 pt-6">
          <x-button :href="route('faq-chatbot-qna.index')" variant="outline">Cancel</x-button>
          <x-button type="submit" variant="primary">Update</x-button>
        </div>
      </form>
    </div>
  </x-card>
@endsection


