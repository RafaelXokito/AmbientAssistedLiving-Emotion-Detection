package pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions;

import javax.ws.rs.core.Response;
import javax.ws.rs.ext.ExceptionMapper;
import javax.ws.rs.ext.Provider;
import java.util.logging.Logger;

@Provider
public class MyUnauthorizedExceptionMapper implements ExceptionMapper<MyUnauthorizedException> {
    private static final Logger logger = Logger.getLogger("exceptions.MyUnauthorizedExceptionMapper");

    @Override
    public Response toResponse(MyUnauthorizedException e) {
        String errorMsg = e.getMessage();
        logger.warning("ERROR: " + errorMsg);
        return Response.status(Response.Status.UNAUTHORIZED).entity(errorMsg).build();
    }
}
