<?php

return [
    'array' => ':attribute phải là một danh sách.',
    'confirmed' => ':attribute nhập lại không khớp.',
    'current_password' => 'Mật khẩu hiện tại không đúng.',
    'date' => ':attribute không phải ngày hợp lệ.',
    'email' => ':attribute phải là email hợp lệ.',
    'enum' => ':attribute không hợp lệ.',
    'exists' => ':attribute không tồn tại.',
    'image' => ':attribute phải là hình ảnh.',
    'in' => ':attribute không hợp lệ.',
    'integer' => ':attribute phải là số nguyên.',
    'max' => [
        'array' => ':attribute không được quá :max phần tử.',
        'file' => ':attribute không được lớn hơn :max KB.',
        'numeric' => ':attribute không được lớn hơn :max.',
        'string' => ':attribute không được dài quá :max ký tự.',
    ],
    'min' => [
        'array' => ':attribute phải có ít nhất :min phần tử.',
        'numeric' => ':attribute phải lớn hơn hoặc bằng :min.',
        'string' => ':attribute phải có ít nhất :min ký tự.',
    ],
    'password' => [
        'letters' => ':attribute phải có ít nhất một chữ cái.',
        'mixed' => ':attribute phải có cả chữ hoa và chữ thường.',
        'numbers' => ':attribute phải có ít nhất một chữ số.',
        'symbols' => ':attribute phải có ít nhất một ký tự đặc biệt.',
        'uncompromised' => ':attribute đã bị lộ trong một vụ rò rỉ dữ liệu, hãy chọn mật khẩu khác.',
    ],
    'required' => 'Vui lòng nhập :attribute.',
    'string' => ':attribute phải là chuỗi ký tự.',
    'unique' => ':attribute đã được sử dụng.',
    'url' => ':attribute phải là đường link hợp lệ.',

    'attributes' => [
        'name' => 'họ tên', 'email' => 'email', 'phone' => 'số điện thoại', 'password' => 'mật khẩu',
        'current_password' => 'mật khẩu hiện tại', 'title' => 'tên phim', 'duration' => 'thời lượng',
        'release_date' => 'ngày khởi chiếu', 'age_rating' => 'phân loại tuổi', 'poster_url' => 'link poster',
        'poster_file' => 'ảnh poster', 'trailer_url' => 'link trailer', 'status' => 'trạng thái',
        'address' => 'địa chỉ', 'city' => 'thành phố', 'cinema_id' => 'rạp', 'room_id' => 'phòng chiếu',
        'movie_id' => 'phim', 'format' => 'định dạng', 'total_rows' => 'số hàng ghế',
        'seats_per_row' => 'số ghế mỗi hàng', 'start_time' => 'giờ bắt đầu', 'base_price' => 'giá vé',
        'seats' => 'ghế', 'method' => 'phương thức thanh toán', 'role' => 'vai trò',
    ],
];
