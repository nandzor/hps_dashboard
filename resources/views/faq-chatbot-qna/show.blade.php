@extends('layouts.app')

@section('title', 'FAQ Detail')
@section('page-title', 'FAQ Detail')

@section('content')
  <x-card>
    <div class="p-6 space-y-6">
      <div>
        <h3 class="text-sm font-medium text-gray-500">Question</h3>
        <p class="mt-1 text-gray-900">{{ $faq->question }}</p>
      </div>

      <div>
        <h3 class="text-sm font-medium text-gray-500">Answer</h3>
        <p class="mt-1 text-gray-900">{{ $faq->answer }}</p>
      </div>

      <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
        <x-button :href="route('faq-chatbot-qna.edit', $faq->id)" variant="primary">Edit</x-button>
        <form action="{{ route('faq-chatbot-qna.destroy', $faq->id) }}" method="POST" onsubmit="return confirm('Delete this FAQ?')">
          @csrf
          @method('DELETE')
          <x-button type="submit" variant="danger">Delete</x-button>
        </form>
      </div>
    </div>
  </x-card>
@endsection


