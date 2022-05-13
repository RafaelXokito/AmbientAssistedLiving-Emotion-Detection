package pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions;

import javax.validation.ConstraintViolationException;

public class MyConstraintViolationException extends Exception {
    public MyConstraintViolationException(ConstraintViolationException e) {
        super(e.getMessage());
    }
}

