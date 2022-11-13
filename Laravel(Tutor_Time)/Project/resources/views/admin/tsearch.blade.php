@extends('layout.main2')
@section('content')
<body style='background-color:bisque;font-size:22px'>
<h2 align='center'>This is Tutor Details</h2>
<table border="5px black"align="center">
<tr>
            <th>Name</th>
            <th>Email</th>
            <th>Id</th>
            <th>Phone Number</th>
            <th>Username</th>
            <th>Gender</th>
            <th>Delete</th>
            <th>Banned</th>
        </tr>
        @foreach($Tutors as $t)
            <tr>
                <td><a href="{{route('Tutor.teacherdetails',
                ['tutor_id'=>$t->tutor_id,'name'=>$t->name,'email'=>$t->email,
                'phone'=>$t->phone,'username'=>$t->username,'gender'=>$t->gender])}}">{{$t->name}}</td>
                <td>{{$t->email}}</td>
                <td>{{$t->tutor_id}}</td>   
                <td>{{$t->phone}}</td>
                <td>{{$t->username}}</td>
                <td>{{$t->gender}}</td>     
                <td><a href="/delete/{{$t->tutor_id}}">Delete</a></td>
                @if($t->status==1)
                <td><a href="{{route('tutors.status.update',
                    ['tutor'=>$t->tutor_id,'status'=>0])}}">Banned</a></td>
                @else
                    <td><a href="{{route('tutors.status.update',
                    ['tutor'=>$t->tutor_id,'status'=>1])}}">Permit</a></td>
               @endif       
            </tr>
        @endforeach
    </table>
    <span>{{$Tutors->links()}}</span>
    <style>.w-5{display:none;}</style>
</body>
    @endsection