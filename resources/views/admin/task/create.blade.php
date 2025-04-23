@extends('admin.layouts.app')

@section('content')

    <div class="absolute z-10">
      <div
        class="w-[487px] rounded-xl shadow-[0px_10px_18px_-2px_rgba(10,9,11,0.07)] outline outline-1 outline-offset-[-1px] outline-Colors-state-stroke-soft-200 inline-flex flex-col justify-start items-start overflow-hidden"
      >
        <div
          class="self-stretch px-5 py-3 bg-Colors-state-bg-white-0 rounded-tl-xl rounded-tr-xl flex flex-col justify-start items-start gap-3 overflow-hidden"
        >
          <div class="self-stretch inline-flex justify-start items-start gap-3">
            <div
              class="flex-1 inline-flex flex-col justify-start items-start gap-1"
            >
              <input type="text" class="text-black text-lg outline-none" />
              <input
                type="text"
                placeholder="Thêm mô tả"
                class="placeholder-Colors-state-text-soft-400 text-black text-sm outline-none"
              />
            </div>
          </div>
          <div class="px-3 py-1 inline-flex justify-start items-center gap-3">
            <select class="text-sm">
              <option>Việc cần làm</option>
            </select>
          </div>
          <div class="inline-flex justify-start items-center gap-3">
            <select
              class="text-sm outline outline-1 outline-Colors-state-stroke-soft-200 rounded-lg px-2 py-1"
            >
              <option>Hôm nay</option>
              <option>Ngày mai</option>
            </select>
            <input
              type="date"
              class="text-sm outline outline-1 outline-Colors-state-stroke-soft-200 rounded-lg px-2 py-1"
            />
            <div class="relative">
              <img
                :src="`/icons/Sidebar_icons/repeat.svg`"
                class="outline outline-1 outline-Colors-state-stroke-soft-200 rounded-lg px-2 py-1"
              />
            </div>
            <div
              class="absolute top-[10rem] left-[14rem] z-50 bg-white outline outline-1 outline-Colors-state-stroke-soft-200 rounded-lg w-[16.5rem] px-2 py-2 flex flex-col gap-2"
            >
              <div class="flex flex-col text-sm gap-1">
                <p>Lặp lại</p>
                <select class="w-full border px-2 py-1 rounded-lg">
                  <option>Không lặp lại</option>
                  <option>Hàng ngày</option>
                  <option>Hàng tuần</option>
                  <option>Hàng tháng</option>
                </select>
              </div>
              <div class="flex flex-col text-sm gap-2">
                <p>Kết thúc</p>
                <div class="inline-flex justify-between items-center">
                  <div class="inline-flex items-center justify-center gap-1">
                    <input type="radio" name="end" id="counter"/>
                    <p>Số lần lặp lại</p>
                  </div>
                  <input type="text" class="border w-8 h-8 text-center">
                </div>
                <div class="inline-flex justify-between items-center">
                  <div class="inline-flex items-center justify-center gap-1">
                    <input type="radio" name="end" id="date"/>
                    <p>Vào ngày</p>
                  </div>
                  <input type="date" class="border px-2 py-1 rounded-lg">
                </div>
              </div>
              <div></div>
            </div>
            <img
              :src="`/icons/tag.svg`"
              class="outline outline-1 outline-Colors-state-stroke-soft-200 rounded-lg px-2 py-1"
            />
          </div>
        </div>
        <div
          class="self-stretch p-5 bg-Colors-state-bg-white-0 rounded-bl-xl rounded-br-xl border-t border-Colors-state-stroke-soft-200 inline-flex justify-between items-center"
        >
          <div class="flex-1 flex justify-end items-center gap-4">
            <div class="flex justify-start items-center gap-4">
              <button
                class="text-sm outline outline-1 outline-Colors-state-stroke-soft-200 rounded-lg px-4 py-2"
              >
                Hủy bỏ
              </button>
              <button class="text-sm bg-orange-100 rounded-lg px-4 py-2">
                Tạo mới
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  
@endsection
