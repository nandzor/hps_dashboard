@extends('layouts.app')

@section('title', 'FAQ Chatbot QnA')
@section('page-title', 'FAQ Chatbot QnA Management')

@section('content')
  <x-card class="mb-4">
    <div class="p-6">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-3 lg:space-y-0">
        <div class="flex-1 max-w-md">
          <form method="GET" action="{{ route('faq-chatbot-qna.index') }}" class="flex">
            <x-input name="search" :value="$search ?? ''" placeholder="Search question/answer..." class="rounded-r-none border-r-0" />
            @if (request()->has('per_page'))
              <input type="hidden" name="per_page" value="{{ request()->get('per_page') }}">
            @endif
            <button type="submit" class="px-6 py-2 bg-gray-600 text-white rounded-r-lg hover:bg-gray-700 transition-colors">Search</button>
          </form>
        </div>

        <div class="flex items-center space-x-4">
          <div class="flex items-center space-x-2">
            <x-per-page-selector :options="$perPageOptions ?? [10,25,50,100]" :current="$perPage ?? 10" :url="route('faq-chatbot-qna.index')" type="server" />
          </div>

          <x-button :href="route('faq-chatbot-qna.create')" variant="primary" size="sm">
            <x-icon name="plus" class="w-4 h-4 mr-1.5" />
            Add FAQ
          </x-button>
        </div>
      </div>
    </div>
  </x-card>

  <x-card>
    <x-table :headers="['Question', 'Answer', 'Created', 'Actions']">
      @forelse($faqs as $faq)
        <tr class="hover:bg-blue-50 transition-colors">
          <td class="px-6 py-4 text-sm text-gray-900">{{ Str::limit($faq->question, 100) }}</td>
          <td class="px-6 py-4 text-sm text-gray-700">{{ Str::limit($faq->answer, 120) }}</td>
          <td class="px-6 py-4 text-sm text-gray-500">{{ optional($faq->created_at)->format('d/m/Y H:i') }}</td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <x-action-dropdown>
              <x-dropdown-link :href="route('faq-chatbot-qna.show', $faq)">👁️ View Details</x-dropdown-link>
              <x-dropdown-link :href="route('faq-chatbot-qna.edit', $faq)">✏️ Edit</x-dropdown-link>
              <x-dropdown-divider />
              <x-dropdown-link :href="route('faq-chatbot-qna.destroy', $faq)" onclick="event.preventDefault(); document.getElementById('delete-form-{{ $faq->id }}').submit();" class="text-red-600 hover:text-red-800">🗑️ Delete</x-dropdown-link>
            </x-action-dropdown>

            <form id="delete-form-{{ $faq->id }}" action="{{ route('faq-chatbot-qna.destroy', $faq) }}" method="POST" class="hidden">
              @csrf
              @method('DELETE')
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="4" class="px-6 py-12 text-center text-gray-500">No FAQ found.</td>
        </tr>
      @endforelse
    </x-table>

    <div class="px-6 py-4 border-t border-gray-200">
      @if ($faqs->hasPages())
        <x-pagination :paginator="$faqs" />
      @endif
    </div>
  </x-card>
@endsection


